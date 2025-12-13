<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        // Redirect to login page (has register tab)
        return redirect()->route('login')->with('tab', 'register');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function showResetPassword($token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }
}
