<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_code',
        'phone',
        'email',
        'lottery_numbers',
        'lottery_selections',
        'number_types',
        'amount',
        'lottery_type',
        'notes',
        'currency',
        'status',
    ];

    protected $casts = [
        'lottery_numbers' => 'array',
        'lottery_selections' => 'array',
        'number_types' => 'array',
    ];
}
