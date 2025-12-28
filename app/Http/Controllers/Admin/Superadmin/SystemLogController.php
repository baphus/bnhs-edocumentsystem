<?php

namespace App\Http\Controllers\Admin\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemLogController extends Controller
{
    /**
     * Display system logs (request logs and email logs).
     */
    public function index(Request $request): Response
    {
        // Request Logs
        $requestLogsQuery = RequestLog::with(['user', 'documentRequest']);

        if ($request->has('action') && $request->action) {
            $requestLogsQuery->where('action', $request->action);
        }

        if ($request->has('user_id') && $request->user_id) {
            $requestLogsQuery->where('user_id', $request->user_id);
        }

        if ($request->has('from_date') && $request->from_date) {
            $requestLogsQuery->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $requestLogsQuery->whereDate('created_at', '<=', $request->to_date);
        }

        $requestLogs = $requestLogsQuery->latest()
            ->paginate(20, ['*'], 'request_logs_page')
            ->withQueryString()
            ->through(fn($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'user_name' => $log->user?->name ?? 'System',
                'tracking_id' => $log->documentRequest?->tracking_id,
                'old_value' => $log->old_value,
                'new_value' => $log->new_value,
                'description' => $log->description,
                'created_at' => $log->created_at,
            ]);

        // Email Logs
        $emailLogsQuery = EmailLog::with('documentRequest');

        if ($request->has('email_status') && $request->email_status) {
            $emailLogsQuery->where('status', $request->email_status);
        }

        if ($request->has('email_from_date') && $request->email_from_date) {
            $emailLogsQuery->whereDate('created_at', '>=', $request->email_from_date);
        }

        if ($request->has('email_to_date') && $request->email_to_date) {
            $emailLogsQuery->whereDate('created_at', '<=', $request->email_to_date);
        }

        $emailLogs = $emailLogsQuery->latest()
            ->paginate(20, ['*'], 'email_logs_page')
            ->withQueryString()
            ->through(fn($log) => [
                'id' => $log->id,
                'recipient_email' => $log->recipient_email,
                'subject' => $log->subject,
                'status' => $log->status,
                'error_message' => $log->error_message,
                'tracking_id' => $log->documentRequest?->tracking_id,
                'sent_at' => $log->sent_at,
                'delivered_at' => $log->delivered_at,
                'created_at' => $log->created_at,
            ]);

        // Get unique actions for filter
        $actions = RequestLog::distinct()->pluck('action');

        // Get users for filter dropdown
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

        return Inertia::render('Admin/Superadmin/Logs/Index', [
            'requestLogs' => $requestLogs,
            'emailLogs' => $emailLogs,
            'actions' => $actions,
            'users' => $users,
            'filters' => $request->only(['action', 'user_id', 'from_date', 'to_date', 'email_status', 'email_from_date', 'email_to_date']),
        ]);
    }
}
