<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsItem extends Model
{
    protected $table = 'cms_items';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeSection($q, string $section)
    {
        return $q->where('section', $section);
    }
}
