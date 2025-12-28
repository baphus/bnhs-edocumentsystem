<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RequestManagementController extends Controller
{
    /**
     * Display list of all requests.
     */
    public function index(Request $request): Response
    {
        $query = DocumentRequest::with('documentType');

        // Search
        if ($request->search) {
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
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by document type
        if ($request->document_type_id) {
            $query->where('document_type_id', $request->document_type_id);
        }

        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->latest()
            ->paginate(20)
            ->through(fn($req) => [
                'id' => $req->id,
                'tracking_id' => $req->tracking_id,
                'first_name' => $req->first_name,
                'middle_name' => $req->middle_name,
                'last_name' => $req->last_name,
                'student_email' => $req->email,
                'lrn' => $req->lrn,
                'grade_level' => $req->grade_level,
                'document_type' => $req->documentType ? ['name' => $req->documentType->name] : null,
                'document_category' => $req->documentType->category ?? null,
                'status' => $req->status,
                'created_at' => $req->created_at,
                'updated_at' => $req->updated_at,
            ]);

        $documentTypes = DocumentType::all();
        
        $statuses = ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'];

        return Inertia::render('Admin/Requests/Index', [
            'requests' => $requests,
            'documentTypes' => $documentTypes,
            'statuses' => $statuses,
            'filters' => $request->only(['search', 'status', 'document_type_id', 'from_date', 'to_date']),
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
        
        return Inertia::render('Admin/Requests/Create', [
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
            'description' => "Admin {$request->user()->name} created request {$documentRequest->tracking_id}",
        ]);

        return redirect()->route('admin.requests.index')
            ->with('success', "Request {$documentRequest->tracking_id} created successfully.");
    }

    /**
     * Show a single request.
     */
    public function show(DocumentRequest $documentRequest): Response
    {
        $documentRequest->load(['documentType', 'logs.user', 'processedBy']);

        return Inertia::render('Admin/Requests/Show', [
            'request' => [
                'id' => $documentRequest->id,
                'tracking_id' => $documentRequest->tracking_id,
                'email' => $documentRequest->email,
                'first_name' => $documentRequest->first_name,
                'middle_name' => $documentRequest->middle_name,
                'last_name' => $documentRequest->last_name,
                'full_name' => $documentRequest->full_name,
                'lrn' => $documentRequest->lrn,
                'grade_level' => $documentRequest->grade_level,
                'section' => $documentRequest->section,
                'track_strand' => $documentRequest->track_strand,
                'school_year_last_attended' => $documentRequest->school_year_last_attended,
                'photo_path' => $documentRequest->photo_path,
                'document_type' => $documentRequest->documentType->name,
                'document_category' => $documentRequest->documentType->category,
                'purpose' => $documentRequest->purpose,
                'status' => $documentRequest->status,
                'admin_notes' => $documentRequest->admin_notes,
                'processed_by' => $documentRequest->processedBy?->name,
                'created_at' => $documentRequest->created_at,
                'updated_at' => $documentRequest->updated_at,
                'logs' => $documentRequest->logs->map(fn($log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'description' => $log->description,
                    'user' => $log->user?->name,
                    'created_at' => $log->created_at,
                ]),
            ],
            'statuses' => ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'],
        ]);
    }

    /**
     * Update request status.
     */
    public function updateStatus(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'])],
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $documentRequest->status;

        $documentRequest->updateStatus(
            $validated['status'],
            $request->user(),
            $validated['notes'] ?? null
        );

        // Update completed_at if status is Completed
        if ($validated['status'] === 'Completed' && !$documentRequest->completed_at) {
            $documentRequest->update(['completed_at' => now()]);
        }

        // Reload relationship for email
        $documentRequest->load('documentType');

        // Send email notification
        try {
            \Illuminate\Support\Facades\Mail::to($documentRequest->email)
                ->send(new \App\Mail\RequestStatusUpdatedMail($documentRequest, $oldStatus));
        } catch (\Exception $e) {
            // Log error but don't fail the status update
            \Log::error('Failed to send status update email: ' . $e->getMessage());
        }

        return back()->with('success', 'Request status updated successfully.');
    }

    /**
     * Update admin notes.
     */
    public function updateNotes(Request $request, DocumentRequest $documentRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $oldNotes = $documentRequest->admin_notes;
        $documentRequest->update(['admin_notes' => $validated['admin_notes']]);

        // Log the change
        $documentRequest->logs()->create([
            'user_id' => $request->user()->id,
            'action' => 'note_updated',
            'old_value' => $oldNotes ? 'Previous note' : null,
            'new_value' => $validated['admin_notes'] ? 'Note updated' : 'Note cleared',
            'description' => $validated['admin_notes'],
        ]);

        return back()->with('success', 'Notes updated successfully.');
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

