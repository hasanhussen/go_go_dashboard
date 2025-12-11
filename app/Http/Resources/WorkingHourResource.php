<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkingHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id"       => $this->id,
            "day"      => $this->day,
            "open_at"  => $this->open_at,
            "close_at" => $this->close_at,
            // "is_open"  => $this->is_open,
            // "is_24"    => $this->is_24,
        ];
    }
}
