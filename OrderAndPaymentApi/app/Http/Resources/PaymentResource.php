<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount_cents' => $this->amount,
            'amount' => $this->amount_formatted,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'attempt_number' => $this->attempt_number,
            'external_reference' => $this->external_reference,
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
