<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryType extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'is_active', 'sort_order'];
    
    protected static function booted()
    {
        static::creating(function ($lotteryType) {
            if (!$lotteryType->sort_order) {
                $maxSortOrder = static::max('sort_order') ?? 0;
                $lotteryType->sort_order = $maxSortOrder + 1;
            }
        });
    }
}
