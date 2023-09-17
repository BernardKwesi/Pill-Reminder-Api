<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DosageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=> $this->id,
			"user_id" => $this->user_id,
			"pill_name"=> $this->pill_name,
			"start_date"=> $this->start_date,
			"dosage_times"=>  json_decode($this->dosage_times, true),
			"medication_quantity"=> $this->medication_quantity,
			"quantity_per_dose"=> $this->quantity_per_dose,
			"dosage_frequency" => $this->dosage_frequency,
			"next_dosage_time" => $this->next_dosage_time,
			"created_at"=> $this->created_at,
			"updated_at"=>$this->updated_at
        ];
    }
}
