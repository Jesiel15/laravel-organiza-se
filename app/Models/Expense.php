<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'icon',
        'color',
        'name_expense',
        'value_expense',
        'date_expense',
        'anotation',
        'is_paid',
    ];

    protected function casts(): array
    {
        return [
            'date_expense' => 'date',
            'value_expense' => 'decimal:2',
            'is_paid' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Filtra despesas de um usuário por mês/ano no formato MMYYYY,
     * equivalente à antiga chave do Map no Mongoose.
     */
    public function scopeForMonthYear($query, string $monthYear)
    {
        $month = (int) substr($monthYear, 0, 2);
        $year = (int) substr($monthYear, 2, 4);

        return $query->whereYear('date_expense', $year)
            ->whereMonth('date_expense', $month);
    }
}
