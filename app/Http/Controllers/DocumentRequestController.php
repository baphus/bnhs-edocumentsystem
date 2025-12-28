<?php

namespace App\Http\Controllers;

use App\Mail\RequestSubmittedMail;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048|dimensions:min_width=200,min_height=200',
            'document_type_id' => 'required|exists:document_types,id',
        ], [
            'lrn.regex' => 'LRN must be exactly 12 digits.',
            'photo.dimensions' => 'Photo must be at least 200x200 pixels.',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // Calculate estimated completion date (default: 7 business days)
        $estimatedCompletionDate = now()->addWeekdays(7);

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
            'photo_path' => $photoPath,
            'document_type_id' => $validated['document_type_id'],
            'purpose' => $validated['purpose'],
            'quantity' => $validated['quantity'] ?? 1,
            'status' => 'Pending',
            'estimated_completion_date' => $estimatedCompletionDate,
            'otp_verified' => true,
        ]);

        // Load document type relationship for email
        $documentRequest->load('documentType');

        // Send email notification
        try {
            Mail::to($validated['email'])->send(new RequestSubmittedMail($documentRequest));
        } catch (\Exception $e) {
            // Log error but don't fail the request submission
            \Log::error('Failed to send request submission email: ' . $e->getMessage());
        }

        // Clear session
        session()->forget(['verified_email', 'verified_at', 'document_type_id']);

        // Redirect to user dashboard verification page
        return redirect()->route('user.dashboard.verify')
            ->with('success', 'Request submitted successfully! Please verify your email to access your dashboard.');
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
    private function getTrackStrands(): array
    {
        return [
            'Academic Track' => [
                'STEM' => 'Science, Technology, Engineering, and Mathematics (STEM)',
                'ABM' => 'Accountancy, Business, and Management (ABM)',
                'HUMSS' => 'Humanities and Social Sciences (HUMSS)',
                'GAS' => 'General Academic Strand (GAS)',
            ],
            'Technical-Vocational-Livelihood Track' => [
                'TVL-HE' => 'TVL - Home Economics',
                'TVL-ICT' => 'TVL - Information and Communications Technology',
                'TVL-IA' => 'TVL - Industrial Arts',
                'TVL-AFA' => 'TVL - Agri-Fishery Arts',
            ],
            'Sports Track' => [
                'Sports' => 'Sports Track',
            ],
            'Arts and Design Track' => [
                'Arts' => 'Arts and Design Track',
            ],
        ];
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

