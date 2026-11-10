<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public const RETAIL_CODE = 'EGP';

    public const RETAIL_SYMBOL = 'ج.م';

    public static function retail(): self
    {
        return self::where('code', self::RETAIL_CODE)->whereNull('deleted_at')->orderBy('id')->firstOrFail();
    }

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code', 'name', 'symbol',
    ];
}
