<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'color' => $this->color,
            'nameRevenue' => $this->name_revenue,
            'valueRevenue' => (float) $this->value_revenue,
            'dateRevenue' => $this->date_revenue?->format('Y-m-d'),
            'anotation' => $this->anotation,
        ];
    }
}
