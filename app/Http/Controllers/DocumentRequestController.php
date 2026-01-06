<?php

namespace App\Http\Controllers;

use App\Mail\RequestSubmittedMail;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DocumentRequestController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Step 1: Show document selection page.
     */
    public function selectDocument(): Response
    {
        $documentTypes = DocumentType::all()->groupBy('category');

        return Inertia::render('Request/SelectDocument', [
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Step 2: Show email verification page.
     */
    public function showEmailVerification(Request $request): Response
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $documentType = DocumentType::findOrFail($request->document_type_id);

        return Inertia::render('Request/EmailVerification', [
            'documentType' => $documentType,
        ]);
    }

    /**
     * Send OTP to email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $result = $this->otpService->sendOtp($request->email, 'request');

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['email' => $result['message']]);
    }

    /**
     * Verify OTP and proceed to form.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $result = $this->otpService->verifyOtp($request->email, $request->otp, 'request');

        if ($result['success']) {
            // Store verified email in session
            session([
                'verified_email' => $request->email,
                'verified_at' => now()->toISOString(),
                'document_type_id' => $request->document_type_id,
            ]);

            return redirect()->route('request.form');
        }

        return back()->withErrors(['otp' => $result['message']]);
    }

    /**
     * Step 3: Show request form (only if OTP verified).
     */
    public function showForm(Request $request): Response|RedirectResponse
    {
        // Check if email is verified
        if (!session('verified_email') || !session('verified_at')) {
            return redirect()->route('request.select')
                ->withErrors(['error' => 'Please verify your email first.']);
        }

        // Check if verification is still valid (30 minutes)
        $verifiedAt = \Carbon\Carbon::parse(session('verified_at'));
        if ($verifiedAt->diffInMinutes(now()) > 30) {
            session()->forget(['verified_email', 'verified_at', 'document_type_id']);
            return redirect()->route('request.select')
                ->withErrors(['error' => 'Session expired. Please verify your email again.']);
        }

        $documentType = DocumentType::findOrFail(session('document_type_id'));

        return Inertia::render('Request/Form', [
            'email' => session('verified_email'),
            'documentType' => $documentType,
            'gradeLevels' => $this->getGradeLevels(),
            'trackStrands' => $this->getTrackStrands(),
            'schoolYears' => $this->getSchoolYears(),
        ]);
    }

    /**
     * Submit the request form.
     */
    public function submit(Request $request)
    {
        // Verify session
        if (!session('verified_email') || session('verified_email') !== $request->email) {
            return redirect()->route('request.select')
                ->withErrors(['error' => 'Invalid session. Please start over.']);
        }

        // Validate form data
        $validated = $request->validate([
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|string|size:12|regex:/^\d{12}$/',
            'grade_level' => ['required', Rule::in(array_keys($this->getGradeLevels()))],
            'section' => 'required|string|max:255',
            'track_strand' => [
                Rule::requiredIf(in_array($request->grade_level, ['Grade 11', 'Grade 12'])),
                'nullable',
                'string',
                'max:255',
            ],
            'school_year_last_attended' => 'required|string|max:20',
            'purpose' => 'required|string|max:1000',
            'quantity' => 'nullable|integer|min:1|max:10',
            'document_type_id' => 'required|exists:document_types,id',
            'signature' => 'required|string',
        ], [
            'lrn.regex' => 'LRN must be exactly 12 digits.',
        ]);

        // Get document type to use its processing_days
        $documentType = DocumentType::findOrFail($validated['document_type_id']);
        $processingDays = $documentType->processing_days ?? 7; // Fallback to 7 if not set

        // Calculate estimated completion date using document type's processing days
        $estimatedCompletionDate = now()->addWeekdays($processingDays);

        // Check for duplicate submission (same email, same document type, pending status within last 24 hours)
        $recentDuplicate = DocumentRequest::where('email', $validated['email'])
            ->where('document_type_id', $validated['document_type_id'])
            ->where('status', 'Pending')
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($recentDuplicate) {
            return back()->withErrors(['error' => 'You have a pending request for this document type. Please wait for it to be processed before submitting a new one.']);
        }

        // Create the document request
        $documentRequest = DocumentRequest::create([
            'email' => $validated['email'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'lrn' => $validated['lrn'],
            'grade_level' => $validated['grade_level'],
            'section' => $validated['section'],
            'track_strand' => $validated['track_strand'],
            'school_year_last_attended' => $validated['school_year_last_attended'],
            'document_type_id' => $validated['document_type_id'],
            'purpose' => $validated['purpose'],
            'quantity' => $validated['quantity'] ?? 1,
            'status' => 'Pending',
            'estimated_completion_date' => $estimatedCompletionDate,
            'otp_verified' => true,
            'signature' => $validated['signature'],
        ]);

        // Load document type relationship for email
        $documentRequest->load('documentType');

        // Send email notification
        try {
            Mail::to($validated['email'])->send(new RequestSubmittedMail($documentRequest));
        } catch (\Exception $e) {
            // Log error but don't fail the request submission
            Log::error('Failed to send request submission email: ' . $e->getMessage());
        }

        // Since email was already verified in the request flow, automatically set dashboard verification
        // This avoids redundant email verification
        session([
            'dashboard_verified_email' => $validated['email'],
            'dashboard_verified_at' => now()->toISOString(),
        ]);

        // Clear request session
        session()->forget(['verified_email', 'verified_at', 'document_type_id']);

        // Redirect directly to dashboard (no need to verify email again)
        return redirect()->route('user.dashboard.index')
            ->with('success', 'Request submitted successfully! You can now view your request in the dashboard.');
    }

    /**
     * Show success page with tracking ID.
     */
    public function success(string $tracking_id): Response
    {
        $request = DocumentRequest::with('documentType')
            ->where('tracking_id', $tracking_id)
            ->firstOrFail();

        return Inertia::render('Request/Success', [
            'request' => [
                'tracking_id' => $request->tracking_id,
                'email' => $request->email,
                'full_name' => $request->full_name,
                'document_type' => $request->documentType->name,
                'status' => $request->status,
                'created_at' => $request->created_at,
            ],
        ]);
    }

    /**
     * Get available grade levels.
     */
    private function getGradeLevels(): array
    {
        return [
            'Grade 7' => 'Grade 7',
            'Grade 8' => 'Grade 8',
            'Grade 9' => 'Grade 9',
            'Grade 10' => 'Grade 10',
            'Grade 11' => 'Grade 11',
            'Grade 12' => 'Grade 12',
        ];
    }

    /**
     * Get available tracks/strands for SHS.
     */
    private function getTrackStrands()
    {
        // Fetch active tracks from DB and group by category
        $tracks = \App\Models\Track::where('is_active', true)
            ->get()
            ->groupBy('category');

        $formatted = [];
        foreach ($tracks as $category => $categoryTracks) {
            $formatted[$category] = $categoryTracks->mapWithKeys(function ($track) {
                return [$track->code => $track->name];
            })->toArray();
        }

        return $formatted;
    }

    /**
     * Get available school years (last 10 years).
     */
    private function getSchoolYears(): array
    {
        $years = [];
        $currentYear = (int) date('Y');
        
        for ($i = 0; $i < 10; $i++) {
            $startYear = $currentYear - $i;
            $endYear = $startYear + 1;
            $years["{$startYear}-{$endYear}"] = "{$startYear}-{$endYear}";
        }

        return $years;
    }
}

