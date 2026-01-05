<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\EmailLog;
use App\Models\RequestLog;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the superadmin dashboard.
     */
    public function index(): Response
    {
        // KPI Cards
        $totalRequests = DocumentRequest::count();
        $pendingRequests = DocumentRequest::where('status', 'Pending')->count();
        $completedRequests = DocumentRequest::where('status', 'Completed')->count();
        $fulfillmentRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 1) : 0;
        
        $totalEmails = EmailLog::count();
        $successfulEmails = EmailLog::whereIn('status', ['sent', 'delivered'])->count();
        $emailSuccessRate = $totalEmails > 0 ? round(($successfulEmails / $totalEmails) * 100, 1) : 0;

        $stats = [
            'total_requests' => $totalRequests,
            'pending_documents' => $pendingRequests,
            'fulfillment_rate' => $fulfillmentRate,
            'email_success_rate' => $emailSuccessRate,
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->orWhereNull('status')->count(),
        ];


        // Activity Feed (recent logs)
        $activityFeed = RequestLog::with(['user', 'documentRequest'])
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'user_name' => $log->user?->name ?? 'System',
                'tracking_id' => $log->documentRequest?->tracking_id,
                'old_value' => $log->old_value,
                'new_value' => $log->new_value,
                'description' => $log->description,
                'created_at' => $log->created_at,
            ]);

        // Recent requests
        $recentRequests = DocumentRequest::with('documentType')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($req) => [
                'id' => $req->id,
                'tracking_id' => $req->tracking_id,
                'full_name' => $req->full_name,
                'email' => $req->email,
                'document_type' => $req->documentType->name ?? 'N/A',
                'status' => $req->status,
                'created_at' => $req->created_at,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'activityFeed' => $activityFeed,
            'recentRequests' => $recentRequests,
        ]);
    }
}
