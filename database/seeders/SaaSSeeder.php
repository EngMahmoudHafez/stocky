<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\SaaS\Plan;
class SaaSSeeder extends Seeder { public function run(): void { if (config('database.default') !== 'central' && config('database.default') !== env('DB_CONNECTION','mysql')) return; foreach([['Starter','starter',0,0,2,50,50,14],['Professional','professional',29.99,299.99,10,500,500,14],['Enterprise','enterprise',99.99,999.99,null,null,null,30]] as $p){Plan::firstOrCreate(['slug'=>$p[1]],['name'=>$p[0],'monthly_price'=>$p[2],'yearly_price'=>$p[3],'max_users'=>$p[4],'max_clients'=>$p[5],'max_products'=>$p[6],'trial_days'=>$p[7],'features'=>[],'is_active'=>true]);}}}
