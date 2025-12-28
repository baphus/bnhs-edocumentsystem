<?php

namespace App\Http\Controllers\Admin\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentTypeController extends Controller
{
    /**
     * Display list of document types.
     */
    public function index(): Response
    {
        $documentTypes = DocumentType::withCount('requests')
            ->latest()
            ->get()
            ->map(fn($dt) => [
                'id' => $dt->id,
                'name' => $dt->name,
                'category' => $dt->category,
                'description' => $dt->description,
                'processing_days' => $dt->processing_days,
                'is_active' => $dt->is_active,
                'requests_count' => $dt->requests_count,
                'created_at' => $dt->created_at,
            ]);

        return Inertia::render('Admin/Superadmin/DocumentTypes/Index', [
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Superadmin/DocumentTypes/Form');
    }

    /**
     * Store new document type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'processing_days' => 'required|integer|min:1|max:30',
            'is_active' => 'boolean',
        ]);

        DocumentType::create($validated);

        return redirect()->route('admin.superadmin.document-types.index')
            ->with('success', 'Document type created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(DocumentType $documentType): Response
    {
        return Inertia::render('Admin/Superadmin/DocumentTypes/Form', [
            'documentType' => [
                'id' => $documentType->id,
                'name' => $documentType->name,
                'category' => $documentType->category,
                'description' => $documentType->description,
                'processing_days' => $documentType->processing_days,
                'is_active' => $documentType->is_active,
            ],
        ]);
    }

    /**
     * Update document type.
     */
    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'processing_days' => 'required|integer|min:1|max:30',
            'is_active' => 'boolean',
        ]);

        $documentType->update($validated);

        return redirect()->route('admin.superadmin.document-types.index')
            ->with('success', 'Document type updated successfully.');
    }

    /**
     * Delete document type (soft delete).
     */
    public function destroy(DocumentType $documentType)
    {
        $documentType->delete();

        return redirect()->route('admin.superadmin.document-types.index')
            ->with('success', 'Document type deleted successfully.');
    }
}
