<?php

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'status' => PaymentStatus::getLabel($this->status),
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'order' => new OrderResource($this->order),
        ];
    }
}
