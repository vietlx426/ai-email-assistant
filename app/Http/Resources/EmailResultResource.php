<?php

namespace App\Http\Resources;

use App\DTOs\AIResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailResultResource extends JsonResource
{
    public function __construct(AIResponse $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'success' => $this->resource->success,
            'data' => $this->when($this->resource->success, [
                'content' => $this->resource->content,
                'usage' => $this->resource->usage,
                'metadata' => $this->resource->metadata,
            ]),
            'error' => $this->when(!$this->resource->success, [
                'message' => $this->resource->error,
            ]),
        ];
    }
}