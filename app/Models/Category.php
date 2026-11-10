<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'name',
        'icon',
        'size_guide_id',
    ];

    public function sizeGuide()
    {
        return $this->belongsTo(SizeGuide::class);
    }

    public function subcategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps();
    }
}
