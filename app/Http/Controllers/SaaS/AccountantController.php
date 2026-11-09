<?php
namespace App\Http\Controllers\SaaS;
use App\Http\Controllers\Controller;
use App\Models\SaaS\{Accountant,Tenant};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class AccountantController extends Controller
{
 public function login(){return view('saas.accountant-login');}
 public function authenticate(Request $r){$d=$r->validate(['email'=>'required|email','password'=>'required']);$a=Accountant::where('email',$d['email'])->where('is_active',1)->first();if(!$a||!Hash::check($d['password'],$a->password))return back()->withErrors(['email'=>'Invalid credentials.']);$r->session()->put('saas_accountant_id',$a->id);return redirect('/accountant');}
 private function me(Request $r){$a=Accountant::find($r->session()->get('saas_accountant_id'));if(!$a)abort(403);return $a;}
 public function dashboard(Request $r){$a=$this->me($r);return view('saas.accountant',['accountant'=>$a,'tenants'=>$a->tenants()->with('plan')->get()]);}
 public function createClient(Request $r){$a=$this->me($r);$d=$r->validate(['name'=>'required','slug'=>'required|alpha_dash|unique:saas_tenants,slug','email'=>'nullable|email','plan_id'=>'required|exists:saas_plans,id']);$d['database_name']=env('SAAS_DB_PREFIX','stocky_tenant_').\Illuminate\Support\Str::slug($d['slug'],'_');$plan=\App\Models\SaaS\Plan::findOrFail($d['plan_id']);$d['status']='trial';$d['trial_ends_at']=now()->addDays($plan->trial_days);$t=Tenant::create($d);$t->accountants()->attach($a->id);\Illuminate\Support\Facades\DB::statement('CREATE DATABASE IF NOT EXISTS `'.$d['database_name'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');\App\Models\SaaS\Subscription::create(['tenant_id'=>$t->id,'plan_id'=>$t->plan_id,'status'=>'trial','billing_cycle'=>'monthly','amount'=>$plan->monthly_price,'trial_ends_at'=>$t->trial_ends_at]);\Artisan::call('saas:provision-tenant',['tenant'=>$t->id]); return back()->with('success','Client created and Stocky database provisioned.');}
 public function open(Request $r,Tenant $tenant){$a=$this->me($r);abort_unless($a->tenants()->whereKey($tenant->id)->exists(),403);$r->session()->put('saas_tenant_id',$tenant->id);$base=env('SAAS_BASE_DOMAIN');$host=$tenant->domain ?: $tenant->slug.'.'.$base;return redirect()->away(($r->isSecure()?'https':'http').'://'.$host.'/next');}
 public function logout(Request $r){$r->session()->forget(['saas_accountant_id','saas_tenant_id']);return redirect('/accountant/login');}
}
