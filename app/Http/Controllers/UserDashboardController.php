<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show email verification page for dashboard access.
     */
    public function showEmailVerification(): Response
    {
        return Inertia::render('User/EmailVerification');
    }

    /**
     * Send OTP to email for dashboard access.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Verify that the email has at least one request
        $hasRequests = DocumentRequest::where('email', $request->email)->exists();

        if (!$hasRequests) {
            return back()->withErrors([
                'email' => 'No requests found for this email address.',
            ]);
        }

        $result = $this->otpService->sendOtp($request->email, 'dashboard');

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['email' => $result['message']]);
    }

    /**
     * Verify OTP and grant dashboard access.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $rules = [
            'email' => 'required|email',
        ];

        if (!$this->isOtpBypassEnabled()) {
            $rules['otp'] = 'required|string|size:6';
        }

        $request->validate($rules);

        if ($this->isOtpBypassEnabled()) {
            session([
                'dashboard_verified_email' => $request->email,
                'dashboard_verified_at' => now()->toISOString(),
            ]);

            return redirect()->route('user.dashboard.index')
                ->with('success', '[DEV] OTP bypass enabled. Dashboard auto-verified.');
        }

        $result = $this->otpService->verifyOtp($request->email, $request->otp, 'dashboard');

        if ($result['success']) {
            // Store verified email in session
            session([
                'dashboard_verified_email' => $request->email,
                'dashboard_verified_at' => now()->toISOString(),
            ]);

            return redirect()->route('user.dashboard.index');
        }

        return back()->withErrors(['otp' => $result['message']]);
    }

    /**
     * Display user dashboard (passwordless - requires email verification).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        // Check if email is verified in session
        if (!session('dashboard_verified_email') || !session('dashboard_verified_at')) {
            return redirect()->route('user.dashboard.verify')
                ->withErrors(['error' => 'Please verify your email first.']);
        }

        // Check if verification is still valid (30 minutes)
        $verifiedAt = \Carbon\Carbon::parse(session('dashboard_verified_at'));
        if ($verifiedAt->diffInMinutes(now()) > 30) {
            session()->forget(['dashboard_verified_email', 'dashboard_verified_at']);
            return redirect()->route('user.dashboard.verify')
                ->withErrors(['error' => 'Session expired. Please verify your email again.']);
        }

        $email = session('dashboard_verified_email');

        // Get all requests for this email, ordered by latest first
        $requests = DocumentRequest::with(['documentType', 'logs.user'])
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
                    'activity_logs' => $req->logs->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'action' => $log->action,
                            'description' => $log->description,
                            'old_value' => $log->old_value,
                            'new_value' => $log->new_value,
                            'user_name' => $log->user?->name ?? 'System',
                            'created_at' => $log->created_at->format('F d, Y h:i A'),
                        ];
                    }),
                ];
            });

        $latestRequest = $requests->first();

        // Get the user's name from the first request
        $userRequest = DocumentRequest::where('email', $email)->first();
        $userName = $userRequest ? $userRequest->first_name : null;

        return Inertia::render('User/Dashboard', [
            'email' => $email,
            'userName' => $userName,
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

    private function isOtpBypassEnabled(): bool
    {
        return (bool) (config('app.dev_tools.enabled') && config('app.dev_tools.bypass_otp'));
    }
}
