<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'status' => $this->status,
            'purpose' => $this->purpose,
            'remarks' => $this->remarks,
            'student' => new StudentResource($this->whenLoaded('student')),
            'document_type' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'activity_logs' => ActivityLogResource::collection($this->whenLoaded('activityLogs')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

