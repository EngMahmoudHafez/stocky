<?php
namespace App\Models\SaaS;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class Accountant extends Authenticatable { use Notifiable; protected $table='saas_accountants'; protected $connection='central'; protected $guarded=[]; protected $hidden=['password']; public function tenants(){return $this->belongsToMany(Tenant::class,'saas_accountant_tenant');} }
