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
use Illuminate\Support\Facades\Log;


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

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['tracking_id', 'first_name', 'last_name', 'lrn', 'document_type_id', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        // Per page pagination
        $perPage = $request->per_page && in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;

        $requests = $query->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString()
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
            'filters' => $request->only(['search', 'status', 'document_type_id', 'from_date', 'to_date', 'sort_by', 'sort_direction', 'per_page']),
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
            'document_request_id' => $documentRequest->id,
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
                'student_email' => $documentRequest->email,
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
                'document_type' => $documentRequest->documentType ? ['name' => $documentRequest->documentType->name] : null,
                'document_category' => $documentRequest->documentType->category ?? null,
                'purpose' => $documentRequest->purpose,
                'status' => $documentRequest->status,
                'admin_notes' => $documentRequest->admin_notes,
                'processed_by' => $documentRequest->processedBy?->name,
                'created_at' => $documentRequest->created_at,
                'updated_at' => $documentRequest->updated_at,
                'request_logs' => $documentRequest->logs->map(fn($log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'description' => $log->description,
                    'user' => $log->user ? ['name' => $log->user->name] : null,
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
            Log::error('Failed to send status update email: ' . $e->getMessage());
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
            'document_request_id' => $documentRequest->id,
            'user_id' => $request->user()->id,
            'action' => 'note_updated',
            'old_value' => $oldNotes ? 'Previous note' : null,
            'new_value' => $validated['admin_notes'] ? 'Note updated' : 'Note cleared',
            'description' => $validated['admin_notes'] ?: 'Notes cleared',
        ]);

        return back()->with('success', 'Notes updated successfully.');
    }

    /**
     * Update a request (for superadmin inline editing).
     */
    public function update(Request $request, DocumentRequest $documentRequest)
    {
        // Only superadmin can update requests
        if ($request->user()->role !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'lrn' => 'nullable|string|size:12|regex:/^\d{12}$/',
            'status' => ['nullable', Rule::in(['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'])],
        ]);

        $oldStatus = $documentRequest->status;
        $changes = [];

        if (isset($validated['first_name']) && $validated['first_name'] !== $documentRequest->first_name) {
            $changes[] = "First name: {$documentRequest->first_name} → {$validated['first_name']}";
            $documentRequest->first_name = $validated['first_name'];
        }

        if (isset($validated['last_name']) && $validated['last_name'] !== $documentRequest->last_name) {
            $changes[] = "Last name: {$documentRequest->last_name} → {$validated['last_name']}";
            $documentRequest->last_name = $validated['last_name'];
        }

        if (isset($validated['email']) && $validated['email'] !== $documentRequest->email) {
            $changes[] = "Email: {$documentRequest->email} → {$validated['email']}";
            $documentRequest->email = $validated['email'];
        }

        if (isset($validated['lrn']) && $validated['lrn'] !== $documentRequest->lrn) {
            $changes[] = "LRN: {$documentRequest->lrn} → {$validated['lrn']}";
            $documentRequest->lrn = $validated['lrn'];
        }

        if (isset($validated['status']) && $validated['status'] !== $documentRequest->status) {
            $documentRequest->updateStatus(
                $validated['status'],
                $request->user(),
                null
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
                Log::error('Failed to send status update email: ' . $e->getMessage());
            }
        } else {
            $documentRequest->save();
        }

        // Log changes if any
        if (!empty($changes) || isset($validated['status'])) {
            $documentRequest->logs()->create([
                'document_request_id' => $documentRequest->id,
                'user_id' => $request->user()->id,
                'action' => 'request_updated',
                'old_value' => null,
                'new_value' => null,
                'description' => !empty($changes) ? implode(', ', $changes) : "Status changed from {$oldStatus} to {$validated['status']}",
            ]);
        }

        return back()->with('success', 'Request updated successfully.');
    }

    /**
     * Bulk update request statuses and/or notes.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'request_ids' => ['required', 'array'],
            'request_ids.*' => ['exists:document_requests,id'],
            'status' => ['nullable', Rule::in(['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'])],
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        // Ensure at least one field is provided
        if (!$validated['status'] && !$validated['admin_notes']) {
            return back()->withErrors(['status' => 'Please provide either a status or notes to update.']);
        }

        $requests = DocumentRequest::whereIn('id', $validated['request_ids'])->get();
        $count = 0;
        $updates = [];

        foreach ($requests as $req) {
            $oldStatus = $req->status;
            $oldNotes = $req->admin_notes;

            // Update status if provided
            if ($validated['status']) {
                $req->updateStatus(
                    $validated['status'],
                    $request->user(),
                    null
                );

                // Update completed_at if status is Completed
                if ($validated['status'] === 'Completed' && !$req->completed_at) {
                    $req->update(['completed_at' => now()]);
                }

                // Reload relationship for email
                $req->load('documentType');

                // Send email notification
                try {
                    \Illuminate\Support\Facades\Mail::to($req->email)
                        ->send(new \App\Mail\RequestStatusUpdatedMail($req, $oldStatus));
                } catch (\Exception $e) {
                    Log::error('Failed to send status update email for request ' . $req->tracking_id . ': ' . $e->getMessage());
                }
            }

            // Update notes if provided
            if (isset($validated['admin_notes'])) {
                $req->update(['admin_notes' => $validated['admin_notes']]);

                // Log the change
                $req->logs()->create([
                    'document_request_id' => $req->id,
                    'user_id' => $request->user()->id,
                    'action' => 'note_updated',
                    'old_value' => $oldNotes ? 'Previous note' : null,
                    'new_value' => $validated['admin_notes'] ? 'Note updated' : 'Note cleared',
                    'description' => $validated['admin_notes'] ?: 'Notes cleared',
                ]);
            }

            $count++;
        }

        // Build success message
        $messageParts = [];
        if ($validated['status']) {
            $messageParts[] = 'status';
        }
        if (isset($validated['admin_notes'])) {
            $messageParts[] = 'notes';
        }
        $message = ucfirst(implode(' and ', $messageParts)) . ' updated for ' . $count . ' request(s) successfully.';

        return back()->with('success', $message);
    }

    /**
     * Bulk delete requests (registrar cannot delete, only superadmin).
     */
    public function bulkDelete(Request $request)
    {
        // Only superadmin can delete
        if ($request->user()->role !== 'superadmin') {
            return back()->with('error', 'Only administrators can delete requests.');
        }

        $validated = $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:document_requests,id',
        ]);

        $count = DocumentRequest::whereIn('id', $validated['request_ids'])->delete();

        foreach ($validated['request_ids'] as $id) {
            RequestLog::create([
                'document_request_id' => $id,
                'user_id' => $request->user()->id,
                'action' => 'request_deleted',
                'description' => "Bulk deleted by {$request->user()->name}",
            ]);
        }

        return back()->with('success', "{$count} request(s) deleted successfully.");
    }

    /**
     * Export requests to CSV.
     */
    public function export(Request $request)
    {
        $query = DocumentRequest::with('documentType');

        // Apply same filters as index
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

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->document_type_id) {
            $query->where('document_type_id', $request->document_type_id);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // If specific IDs are provided, export only those
        if ($request->has('ids') && is_array($request->ids)) {
            $query->whereIn('id', $request->ids);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        $filename = 'requests_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tracking ID', 'Name', 'Email', 'LRN', 'Document Type', 'Status', 'Created At']);

            foreach ($requests as $req) {
                fputcsv($file, [
                    $req->tracking_id,
                    $req->full_name,
                    $req->email,
                    $req->lrn,
                    $req->documentType->name ?? 'N/A',
                    $req->status,
                    $req->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

