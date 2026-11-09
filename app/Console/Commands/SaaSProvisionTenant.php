<?php
namespace App\Console\Commands;
use App\Models\SaaS\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class SaaSProvisionTenant extends Command
{
 protected $signature='saas:provision-tenant {tenant : Tenant ID or slug}';
 protected $description='Create and migrate the database for a Stocky SaaS tenant';
 public function handle(): int {
  $t=Tenant::where('id',$this->argument('tenant'))->orWhere('slug',$this->argument('tenant'))->first();
  if(!$t){$this->error('Tenant not found');return self::FAILURE;}
  DB::statement('CREATE DATABASE IF NOT EXISTS `'.$t->database_name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
  $old=config('database.default');
  config(['database.connections.tenant'=>array_merge(config('database.connections.mysql'),['database'=>$t->database_name,'username'=>$t->database_username ?: env('DB_USERNAME'),'password'=>$t->database_password ?? env('DB_PASSWORD')])]);
  DB::purge('tenant'); config(['database.default'=>'tenant']);
  \Artisan::call('migrate',['--database'=>'tenant','--path'=>'database/migrations','--force'=>true]);
  $this->line(\Artisan::output());
  \Artisan::call('db:seed',['--database'=>'tenant','--class'=>'Database\\Seeders\\DatabaseSeeder','--force'=>true]);
  config(['database.default'=>$old]); $this->info('Tenant provisioned: '.$t->slug); return self::SUCCESS;
 }
}
