<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override the default login method to log information for debugging.
     */
    public function login(\Illuminate\Http\Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Log the credentials for debugging (do not log passwords in production)
        Log::info('Attempting login with credentials', ['email' => $credentials['email']]);

        if (Auth::attempt($credentials)) {
            Log::info('User authenticated successfully', ['user' => Auth::user()]);
            return redirect()->intended($this->redirectTo); // This should go to '/dashboard'

        } else {
            Log::error('Authentication failed for email', ['email' => $credentials['email']]);
            return redirect()->back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }
    }
}
