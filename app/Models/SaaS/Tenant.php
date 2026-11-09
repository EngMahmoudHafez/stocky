<?php
namespace App\Models\SaaS;
use Illuminate\Database\Eloquent\Model;
class Tenant extends Model { protected $table='saas_tenants'; protected $connection='central'; protected $guarded=[]; protected $casts=['settings'=>'array','trial_ends_at'=>'datetime','ends_at'=>'datetime','last_login_at'=>'datetime']; public function plan(){return $this->belongsTo(Plan::class,'plan_id');} public function accountants(){return $this->belongsToMany(Accountant::class,'saas_accountant_tenant');} public function subscriptions(){return $this->hasMany(Subscription::class,'tenant_id');} }
