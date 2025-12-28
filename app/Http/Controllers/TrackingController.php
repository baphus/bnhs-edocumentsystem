<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show tracking page.
     */
    public function index(): Response
    {
        return Inertia::render('Track/Index');
    }

    /**
     * Send OTP for tracking verification.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'tracking_id' => 'required|string',
        ]);

        // Check if request exists with this email and tracking ID
        $documentRequest = DocumentRequest::where('email', $request->email)
            ->where('tracking_id', strtoupper($request->tracking_id))
            ->first();

        if (!$documentRequest) {
            return back()->withErrors([
                'tracking_id' => 'No request found with this email and tracking ID combination.',
            ]);
        }

        $result = $this->otpService->sendOtp($request->email, 'tracking');

        if ($result['success']) {
            return back()->with([
                'success' => $result['message'],
                'otp_sent' => true,
            ]);
        }

        return back()->withErrors(['email' => $result['message']]);
    }

    /**
     * Verify OTP and show tracking result.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'tracking_id' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        // Verify OTP
        $result = $this->otpService->verifyOtp($request->email, $request->otp, 'tracking');

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        // Get the request
        $documentRequest = DocumentRequest::with(['documentType', 'logs.user'])
            ->where('email', $request->email)
            ->where('tracking_id', strtoupper($request->tracking_id))
            ->firstOrFail();

        return Inertia::render('Track/Result', [
            'request' => [
                'tracking_id' => $documentRequest->tracking_id,
                'full_name' => $documentRequest->full_name,
                'email' => $documentRequest->email,
                'lrn' => $documentRequest->lrn,
                'grade_level' => $documentRequest->grade_level,
                'section' => $documentRequest->section,
                'document_type' => $documentRequest->documentType->name,
                'document_category' => $documentRequest->documentType->category,
                'purpose' => $documentRequest->purpose,
                'status' => $documentRequest->status,
                'admin_notes' => $documentRequest->admin_notes,
                'created_at' => $documentRequest->created_at,
                'updated_at' => $documentRequest->updated_at,
                'logs' => $documentRequest->logs->map(fn($log) => [
                    'action' => $log->action,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'description' => $log->description,
                    'created_at' => $log->created_at,
                    'user' => $log->user?->name,
                ]),
            ],
        ]);
    }

    /**
     * Quick track from landing page (simplified, shows basic status).
     */
    public function quickTrack(Request $request, string $tracking_id)
    {
        $documentRequest = DocumentRequest::with('documentType')
            ->where('tracking_id', strtoupper($tracking_id))
            ->first();

        if (!$documentRequest) {
            return back()->withErrors([
                'tracking_id' => 'Request not found. Please check your Tracking ID.',
            ]);
        }

        // Return basic info (no OTP required for basic status check)
        return Inertia::render('Welcome', [
            'canLogin' => true,
            'trackingResult' => [
                'tracking_id' => $documentRequest->tracking_id,
                'status' => $documentRequest->status,
                'document_type' => [
                    'name' => $documentRequest->documentType->name,
                    'category' => $documentRequest->documentType->category,
                ],
                'created_at' => $documentRequest->created_at,
                'updated_at' => $documentRequest->updated_at,
            ],
        ]);
    }
}

