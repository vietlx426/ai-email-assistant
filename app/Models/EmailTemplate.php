<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'content',
        'placeholders',
        'metadata',
        'usage_count',
    ];

    protected $casts = [
        'placeholders' => AsCollection::class,
        'metadata' => AsCollection::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePopular($query)
    {
        return $query->where('usage_count', '>', 10);
    }
}
