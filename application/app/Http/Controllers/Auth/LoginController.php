<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        Log::info('Attempting login with credentials', ['email' => $credentials['email']]);

        if (Auth::attempt($credentials)) {
            Log::info('User authenticated successfully', ['user' => Auth::user()]);
            return redirect()->intended($this->redirectTo);
        }

        Log::warning('Authentication failed for email', ['email' => $credentials['email']]);
        return redirect()->back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput();
    }
}
