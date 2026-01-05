<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrashController extends Controller
{
    /**
     * Display trashed requests.
     */
    public function requests(Request $request): Response
    {
        $query = DocumentRequest::onlyTrashed()->with('documentType');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest('deleted_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($req) => [
                'id' => $req->id,
                'tracking_id' => $req->tracking_id,
                'full_name' => $req->full_name,
                'email' => $req->email,
                'document_type' => $req->documentType->name ?? 'N/A',
                'status' => $req->status,
                'deleted_at' => $req->deleted_at,
            ]);

        return Inertia::render('Admin/Superadmin/Requests/Trash', [
            'requests' => $requests,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Restore a deleted request.
     */
    public function restoreRequest(Request $request, $id)
    {
        $documentRequest = DocumentRequest::onlyTrashed()->findOrFail($id);
        $documentRequest->restore();

        return back()->with('success', 'Request restored successfully.');
    }

    /**
     * Permanently delete a request.
     */
    public function forceDeleteRequest(Request $request, $id)
    {
        $documentRequest = DocumentRequest::onlyTrashed()->findOrFail($id);
        $documentRequest->forceDelete();

        return back()->with('success', 'Request permanently deleted.');
    }
}
