<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Where to redirect users after login.
     */
    protected $redirectTo = '/next';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the login form.
     * ResolveSaaSTenant middleware has already switched to the tenant DB
     * if we're on a tenant subdomain.
     */
 public function showLoginForm()
{
    \Log::info('LOGIN PAGE LOADED', [
        'host' => request()->getHost(),
        'db'   => DB::connection()->getDatabaseName(),
    ]);

    try {
        $app_settings = DB::table('settings')->first();
    } catch (\Throwable $e) {
        $app_settings = null;
    }

    // Fallback defaults if no settings row exists
    $app_settings = $app_settings ?: (object) [
        'app_name' => 'Stocky',
        'logo'     => 'logo.png',
        'favicon'  => 'favicon.ico',
    ];

    return view('auth.login', compact('app_settings'));
}

    /**
     * Handle login. Central admin OR tenant owner — decided by which
     * DB is currently active (set by ResolveSaaSTenant middleware).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $isTenant = DB::getDefaultConnection() === 'tenant'
                 || $request->attributes->has('saasTenant')
                 || session('saas_tenant_id');

        if ($isTenant) {
            return $this->loginAgainstTenant($request);
        }

        // Central admin login
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectTo);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Invalid credentials.']);
    }

    /**
     * Log in a user against the tenant DB (already the default connection).
     */
    protected function loginAgainstTenant(Request $request)
    {
        $query = DB::table('users')->where('email', $request->email);

        if (\Schema::hasColumn('users', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $user = $query->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with that email.']);
        }

        // Tenant admins created by older SaaS code could exist without an Owner
        // role, which made every permission-gated Next menu item disappear.
        // Repair only the tenant owner account, never arbitrary roleless users.
        $tenant = $request->attributes->get('saasTenant');
        if ($tenant && strcasecmp((string) $tenant->email, (string) $user->email) === 0
            && \Schema::hasTable('roles') && \Schema::hasTable('role_user')) {
            $hasRole = DB::table('role_user')->where('user_id', $user->id)->exists();
            if (!$hasRole) {
                $ownerRole = DB::table('roles')->where('id', 1)->orWhere('name', 'Owner')->first();
                if ($ownerRole) {
                    DB::table('users')->where('id', $user->id)->update(['role_id' => $ownerRole->id]);
                    DB::table('role_user')->insertOrIgnore([
                        'user_id' => $user->id,
                        'role_id' => $ownerRole->id,
                    ]);
                    $user->role_id = $ownerRole->id;
                }
            }
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Incorrect password.']);
        }

        // Handle different "active" column names
        foreach (['status', 'statut', 'is_active'] as $col) {
            if (\Schema::hasColumn('users', $col) && isset($user->$col) && (int) $user->$col !== 1) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your account is not active.']);
            }
        }

        Auth::guard('web')->loginUsingId($user->id, $request->filled('remember'));
        $request->session()->regenerate();

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}