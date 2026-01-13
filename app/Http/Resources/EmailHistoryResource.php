<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'operation' => $this->operation,
            'input' => $this->when($request->input('include_content', false), $this->input),
            'output' => $this->when($request->input('include_content', false), $this->output),
            'tone' => $this->tone,
            'rating' => $this->rating,
            'feedback' => $this->feedback,
            'usage' => $this->ai_usage,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}