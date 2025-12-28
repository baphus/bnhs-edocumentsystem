<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        $stats = [
            'total' => DocumentRequest::count(),
            'pending' => DocumentRequest::where('status', 'Pending')->count(),
            'verified' => DocumentRequest::where('status', 'Verified')->count(),
            'processing' => DocumentRequest::where('status', 'Processing')->count(),
            'ready' => DocumentRequest::where('status', 'Ready')->count(),
            'completed' => DocumentRequest::where('status', 'Completed')->count(),
            'rejected' => DocumentRequest::where('status', 'Rejected')->count(),
        ];

        $recentRequests = DocumentRequest::with('documentType')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($req) => [
                'id' => $req->id,
                'tracking_id' => $req->tracking_id,
                'first_name' => $req->first_name,
                'last_name' => $req->last_name,
                'email' => $req->email,
                'document_type' => $req->documentType ? ['name' => $req->documentType->name] : null,
                'status' => $req->status,
                'created_at' => $req->created_at,
            ]);

        $documentTypeStats = DocumentType::withCount('requests')
            ->get()
            ->map(fn($dt) => [
                'name' => $dt->name,
                'category' => $dt->category,
                'count' => $dt->requests_count,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'documentTypeStats' => $documentTypeStats,
        ]);
    }
}

