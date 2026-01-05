<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

/**
 * Example job for processing document requests asynchronously.
 * Demonstrates proper queue job structure for production.
 */
class ProcessDocumentRequest extends BaseJob
{
    protected int $documentRequestId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $documentRequestId)
    {
        $this->documentRequestId = $documentRequestId;
        $this->queue = 'default';
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing document request: ' . $this->documentRequestId);

        // Add your job logic here
        // Example:
        // $request = DocumentRequest::find($this->documentRequestId);
        // Process the request...
    }
}
