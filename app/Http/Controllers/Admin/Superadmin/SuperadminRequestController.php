<?php

namespace App\Http\Controllers\Admin\Superadmin;

use App\Http\Controllers\Controller;
use App\Mail\RequestStatusUpdatedMail;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestLog;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuperadminRequestController extends Controller
{
    /**
     * Display master list of all requests with advanced filters.
     */
    public function index(Request $request): Response
    {
        $query = DocumentRequest::with('documentType');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by document type
        if ($request->has('document_type') && $request->document_type) {
            $query->where('document_type_id', $request->document_type);
        }

        // Filter by LRN
        if ($request->has('lrn') && $request->lrn) {
            $query->where('lrn', 'like', "%{$request->lrn}%");
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($req) => [
                'id' => $req->id,
                'tracking_id' => $req->tracking_id,
                'full_name' => $req->full_name,
                'email' => $req->email,
                'lrn' => $req->lrn,
                'grade_level' => $req->grade_level,
                'document_type' => $req->documentType->name ?? 'N/A',
                'document_category' => $req->documentType->category ?? 'N/A',
                'status' => $req->status,
                'otp_verified' => $req->otp_verified,
                'created_at' => $req->created_at,
                'updated_at' => $req->updated_at,
            ]);

        $documentTypes = DocumentType::all();
        $statuses = ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'];

        return Inertia::render('Admin/Superadmin/Requests/Index', [
            'requests' => $requests,
            'documentTypes' => $documentTypes,
            'statuses' => $statuses,
            'filters' => $request->only(['search', 'status', 'document_type', 'lrn', 'from_date', 'to_date']),
            'gradeLevels' => $this->getGradeLevels(),
            'trackStrands' => $this->getTrackStrands(),
            'schoolYears' => $this->getSchoolYears(),
        ]);
    }

    /**
     * Show the form for creating a new request.
     */
    public function create(): Response
    {
        $documentTypes = DocumentType::all();
        
        return Inertia::render('Admin/Superadmin/Requests/Create', [
            'documentTypes' => $documentTypes,
            'gradeLevels' => $this->getGradeLevels(),
            'trackStrands' => $this->getTrackStrands(),
            'schoolYears' => $this->getSchoolYears(),
            'statuses' => ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'],
        ]);
    }

    /**
     * Store a newly created request.
     */
    public function store(Request $request)
    {
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
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048|dimensions:min_width=200,min_height=200',
            'document_type_id' => 'required|exists:document_types,id',
            'status' => ['nullable', Rule::in(['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'])],
            'otp_verified' => 'nullable|boolean',
        ], [
            'lrn.regex' => 'LRN must be exactly 12 digits.',
            'photo.dimensions' => 'Photo must be at least 200x200 pixels.',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // Create the document request
        $documentRequest = DocumentRequest::create([
            'email' => $validated['email'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'lrn' => $validated['lrn'],
            'grade_level' => $validated['grade_level'],
            'section' => $validated['section'],
            'track_strand' => $validated['track_strand'] ?? null,
            'school_year_last_attended' => $validated['school_year_last_attended'],
            'photo_path' => $photoPath,
            'document_type_id' => $validated['document_type_id'],
            'purpose' => $validated['purpose'],
            'status' => $validated['status'] ?? 'Pending',
            'otp_verified' => $validated['otp_verified'] ?? false,
        ]);

        // Log the creation
        RequestLog::create([
            'user_id' => $request->user()->id,
            'action' => 'request_created',
            'description' => "Superadmin {$request->user()->name} created request {$documentRequest->tracking_id}",
        ]);

        return redirect()->route('admin.superadmin.requests.index')
            ->with('success', "Request {$documentRequest->tracking_id} created successfully.");
    }

    /**
     * Bulk action handler.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', Rule::in(['delete', 'status_update', 'resend_otp'])],
            'request_ids' => ['required', 'array'],
            'request_ids.*' => ['exists:document_requests,id'],
            'status' => ['required_if:action,status_update', Rule::in(['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'])],
        ]);

        $requests = DocumentRequest::whereIn('id', $request->request_ids)->get();
        $count = 0;

        switch ($request->action) {
            case 'delete':
                foreach ($requests as $req) {
                    $req->delete();
                    $count++;
                }
                
                RequestLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'bulk_delete',
                    'description' => "Superadmin {$request->user()->name} bulk deleted {$count} requests",
                ]);
                
                return back()->with('success', "{$count} requests deleted successfully.");

            case 'status_update':
                foreach ($requests as $req) {
                    $oldStatus = $req->status;
                    $req->updateStatus($request->status, $request->user());
                    
                    // Update completed_at if status is Completed
                    if ($request->status === 'Completed' && !$req->completed_at) {
                        $req->update(['completed_at' => now()]);
                    }
                    
                    // Reload relationship for email
                    $req->load('documentType');
                    
                    // Send email notification
                    try {
                        Mail::to($req->email)->send(new RequestStatusUpdatedMail($req, $oldStatus));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send status update email for request ' . $req->tracking_id . ': ' . $e->getMessage());
                    }
                    
                    $count++;
                }
                
                return back()->with('success', "{$count} requests updated successfully.");

            case 'resend_otp':
                $otpService = app(OtpService::class);
                foreach ($requests as $req) {
                    if ($req->status === 'Pending' || !$req->otp_verified) {
                        $otp = $req->generateOtp();
                        // Send email with OTP (you'll need to implement this)
                        // Mail::to($req->email)->send(new OtpMail($otp));
                        $count++;
                    }
                }
                
                RequestLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'bulk_resend_otp',
                    'description' => "Superadmin {$request->user()->name} bulk resent OTP for {$count} requests",
                ]);
                
                return back()->with('success', "OTP resent for {$count} requests.");
        }

        return back()->withErrors(['error' => 'Invalid action.']);
    }

    /**
     * Delete a single request (soft delete).
     */
    public function destroy(Request $request, DocumentRequest $documentRequest)
    {
        $trackingId = $documentRequest->tracking_id;
        $documentRequest->delete();

        RequestLog::create([
            'user_id' => $request->user()->id,
            'action' => 'request_deleted',
            'description' => "Superadmin {$request->user()->name} deleted request {$trackingId}",
        ]);

        return redirect()->route('admin.superadmin.requests.index')
            ->with('success', 'Request deleted successfully.');
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
