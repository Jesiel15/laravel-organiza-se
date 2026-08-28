<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'icon',
        'color',
        'name_revenue',
        'value_revenue',
        'date_revenue',
        'anotation',
    ];

    protected function casts(): array
    {
        return [
            'date_revenue' => 'date',
            'value_revenue' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMonthYear($query, string $monthYear)
    {
        $month = (int) substr($monthYear, 0, 2);
        $year = (int) substr($monthYear, 2, 4);

        return $query->whereYear('date_revenue', $year)
            ->whereMonth('date_revenue', $month);
    }
}
