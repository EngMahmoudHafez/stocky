<?php

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Model;

class SuperAdmin extends Model
{
    protected $table = 'saas_super_admins';
    
    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_primary', 'is_active'
    ];
    
    protected $hidden = ['password'];
}