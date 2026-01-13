<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class EmailHistory extends Model
{
    use HasFactory;

    protected $table = 'email_history';

    protected $fillable = [
        'operation',
        'input',
        'output',
        'tone',
        'ai_usage',
        'metadata',
        'rating',
        'feedback',
    ];

    protected $casts = [
        'ai_usage' => AsCollection::class,
        'metadata' => AsCollection::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeRated($query)
    {
        return $query->whereNotNull('rating');
    }

    public function scopeHighRated($query, int $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }
}