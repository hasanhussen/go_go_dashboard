<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
                "id"=> $this->id,
                "name"=> $this->name,
                "user_id"=> $this->user_id,
                "company_type"=> $this->category_id,
                "city_id"=> $this->city_id,
                "delivery"=> $this->delivery,
                "image"=> $this->image,
                "cover"=> $this->cover,
                "special"=> $this->special,
                "delete_reason"=> $this->delete_reason,
                "ban_reason"=> $this->ban_reason ?? null,
                "ban_until" => $this->ban_until != null? $this->ban_until->format('Y-m-d') : null,
                "address"=> $this->address,
                "phone"=>$this->phone,
                "followers"=>$this->followers,
                "status"=>$this->status,
                "updated_at"=> $this->updated_at->format('Y-m-d'),
                "created_at"=> $this->created_at->format('Y-m-d'),
                //"avg_rating"=> $this->avg_rating??0,
                "total_ratings"=> $this->total_ratings??0,
                "bayesian_score"=> $this->bayesian_score??0.0,
                "company_owner"=> new UserResource($this->whenLoaded('user')),
                "working_hours" => WorkingHourResource::collection(
                   $this->whenLoaded('workingHours')
                ),
                "is_open_now" => $this->isOpenNow(),

        ];
    }
}
