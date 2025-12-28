<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    /**
     * Display user dashboard (passwordless - requires email verification).
     */
    public function index(Request $request): Response
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Get all requests for this email, ordered by latest first
        $requests = DocumentRequest::with('documentType')
            ->where('email', $email)
            ->latest()
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'tracking_id' => $req->tracking_id,
                    'document_type' => $req->documentType->name,
                    'document_category' => $req->documentType->category,
                    'purpose' => $req->purpose,
                    'quantity' => $req->quantity ?? 1,
                    'status' => $req->status,
                    'status_description' => $this->getStatusDescription($req->status),
                    'estimated_completion_date' => $req->estimated_completion_date?->format('F d, Y'),
                    'completed_at' => $req->completed_at?->format('F d, Y h:i A'),
                    'created_at' => $req->created_at->format('F d, Y h:i A'),
                    'updated_at' => $req->updated_at->format('F d, Y h:i A'),
                    'admin_notes' => $req->admin_notes,
                ];
            });

        $latestRequest = $requests->first();

        return Inertia::render('User/Dashboard', [
            'email' => $email,
            'latestRequest' => $latestRequest,
            'requestHistory' => $requests->skip(1)->values(),
            'hasRequests' => $requests->count() > 0,
        ]);
    }

    /**
     * Get status description based on status.
     */
    private function getStatusDescription(string $status): string
    {
        return match ($status) {
            'Pending' => 'Your request is currently under review.',
            'Verified' => 'Your request has been verified and is ready for processing.',
            'Processing' => 'Your document is currently being prepared.',
            'Ready' => 'Your document is ready for pickup!',
            'Completed' => 'Your request has been completed successfully.',
            'Rejected' => 'Your request has been rejected. Please check the admin notes for details.',
            default => 'Status update available.',
        };
    }
}
