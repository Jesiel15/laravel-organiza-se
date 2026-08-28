<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Traduz os campos snake_case do banco para o camelCase que o frontend
 * Angular espera (mesmo formato que o Node/Mongoose original devolvia).
 */
class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'color' => $this->color,
            'nameExpense' => $this->name_expense,
            'valueExpense' => (float) $this->value_expense,
            'dateExpense' => $this->date_expense?->format('Y-m-d'),
            'anotation' => $this->anotation,
            'isPaid' => (bool) $this->is_paid,
        ];
    }
}
