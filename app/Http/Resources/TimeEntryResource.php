<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'time_start' => $this->time_start->format('g:i A'),
            'time_end' => ($this->time_end ? $this->time_end->format('g:i A') : 'N/A'),
        ];
    }
}
