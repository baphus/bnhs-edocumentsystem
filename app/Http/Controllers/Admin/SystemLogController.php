<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmailLog;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SystemLogController extends Controller
{
    /**
     * Display unified system logs.
     */
    public function index(Request $request): Response
    {
        // 1. Audit Logs Query
        $auditQuery = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select([
                'audit_logs.id',
                'audit_logs.created_at',
                DB::raw("'Audit' as source"),
                'users.name as causer',
                'audit_logs.action',
                'audit_logs.model_type as module',
                'audit_logs.description',
                'audit_logs.new_values as details',
                DB::raw("NULL as subject"),
            ]);

        // 2. Request Logs Query
        $requestQuery = DB::table('request_logs')
            ->leftJoin('users', 'request_logs.user_id', '=', 'users.id')
            ->leftJoin('document_requests', 'request_logs.document_request_id', '=', 'document_requests.id')
            ->select([
                'request_logs.id',
                'request_logs.created_at',
                DB::raw("'Request' as source"),
                'users.name as causer',
                'request_logs.action',
                DB::raw("'Document Request' as module"),
                'request_logs.description',
                DB::raw("NULL as details"), // Use description or separate fetch if needed
                'document_requests.tracking_id as subject',
            ]);
            
        // 3. Email Logs Query
        $emailQuery = DB::table('email_logs')
            ->select([
                'email_logs.id',
                'email_logs.created_at',
                DB::raw("'Email' as source"),
                'email_logs.recipient_email as causer',
                'email_logs.status as action',
                'email_logs.error_message as module', // Reusing module col for error/extra info
                'email_logs.error_message as description',
                DB::raw("NULL as details"),
                'email_logs.subject as subject',
            ]);

        // Apply filters
        if ($request->search) {
            $term = '%'.$request->search.'%';
            $auditQuery->where(function($q) use ($term) {
                $q->where('audit_logs.description', 'like', $term)
                  ->orWhere('users.name', 'like', $term);
            });
            $requestQuery->where(function($q) use ($term) {
                $q->where('request_logs.description', 'like', $term)
                  ->orWhere('users.name', 'like', $term);
            });
            $emailQuery->where(function($q) use ($term) {
                $q->where('email_logs.subject', 'like', $term)
                  ->orWhere('email_logs.recipient_email', 'like', $term);
            });
        }
        
        if ($request->date_from) {
            $auditQuery->whereDate('audit_logs.created_at', '>=', $request->date_from);
            $requestQuery->whereDate('request_logs.created_at', '>=', $request->date_from);
            $emailQuery->whereDate('email_logs.created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $auditQuery->whereDate('audit_logs.created_at', '<=', $request->date_to);
            $requestQuery->whereDate('request_logs.created_at', '<=', $request->date_to);
            $emailQuery->whereDate('email_logs.created_at', '<=', $request->date_to);
        }

        // Filter by source
        $queries = [];
        if (!$request->source || $request->source === 'Audit') $queries[] = $auditQuery;
        if (!$request->source || $request->source === 'Request') $queries[] = $requestQuery;
        if (!$request->source || $request->source === 'Email') $queries[] = $emailQuery;
        
        if (count($queries) > 0) {
            $finalQuery = array_shift($queries);
            foreach ($queries as $q) {
                $finalQuery->unionAll($q);
            }
            
            // Wrap in a subquery to avoid "ambiguous column name" errors when ordering
            $logs = DB::query()
                ->fromSub($finalQuery, 'unified_logs')
                ->orderBy('created_at', 'desc')
                ->paginate(20)
                ->withQueryString();
        } else {
            // Fallback empty paginator
            $logs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        // Get users for filter
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('Admin/Logs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'source']),
            'users' => $users,
        ]);
    }
}
