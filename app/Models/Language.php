<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    public const SUPPORTED_LOCALES = ['ar', 'en'];

    public function scopeSupported(Builder $query): Builder
    {
        return $query->whereIn('locale', self::SUPPORTED_LOCALES);
    }

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'locale', 'flag', 'is_default', 'is_active',
    ];

    protected $casts = [
        'is_default' => 'integer',
        'is_active' => 'integer',
    ];
}
