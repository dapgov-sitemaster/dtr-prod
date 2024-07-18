<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'hris_number' => $this->hris_number,
            'name' => $this->full_name,
            'department' => $this->department->description,
            'identity_photo_path' => $this->identity_photo_path,
            'time_entry' => now()->format('g:i A'),
        ];
    }
}
