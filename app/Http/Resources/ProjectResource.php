<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'client_name' => $this->client_name,
            'contractor_name' => $this->contractor_name,
            'contract_amount' => $this->contract_amount,
            'formatted_amount' => number_format($this->contract_amount, 2) . ' ETB',
            'status' => $this->status,
            'status_label' => $this->status?->label(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'duration' => $this->getDuration(),
            'description' => $this->description,
            'boq_count' => $this->whenLoaded('boqItems', fn() => $this->boqItems->count()),
            'ipc_count' => $this->whenLoaded('ipcs', fn() => $this->ipcs->count()),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    protected function getDuration(): ?string
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date) . ' days';
    }
}
