<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\AuditLogService;

class AuthController extends Controller
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    // Show login page
    public function showLogin()
    {
        return view('auth.login');
    }

    // Show register page
    public function showRegister()
    {
        return redirect()->route('login')->with('tab', 'register');
    }

    // Show forgot password page
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Show reset password page
    public function showResetPassword($token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    // Handle login (creates session + token)
    public function login(Request $request)
    {
        // Use manual validator to ensure JSON response
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa input Anda.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $credentials = $request->only(['email', 'password']);

        // Attempt authentication
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated.'
                ], 403);
            }
            
            // Create Sanctum token for API calls
            $token = $user->createToken('web-session')->plainTextToken;
            
            // Log successful login
            $this->auditLog->logLogin($user);
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'redirect' => route('dashboard')
            ]);
        }
        
        // Login failed - check if user exists for better error message
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar. Silakan periksa kembali atau daftar akun baru.'
            ], 401);
        }
        
        // User exists but password is wrong
        return response()->json([
            'success' => false,
            'message' => 'Password salah. Silakan coba lagi.'
        ], 401);
    }

    // Handle registration
    public function register(Request $request)
    {
        // Use manual validator to ensure JSON response
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa input Anda.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $validated = $request->only(['name', 'email', 'password']);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_MEMBER, // Default role
            'is_active' => true,
        ]);

        // Auto-login the user
        Auth::login($user);
        $request->session()->regenerate();
        
        // Create token for API calls
        $token = $user->createToken('web-session')->plainTextToken;
        
        // Log registration
        $this->auditLog->log(
            'user_registered',
            'User',
            $user->id,
            null,
            $user->id
        );

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'redirect' => route('dashboard')
        ]);
    }

    // Handle logout
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Delete current access token
        if ($user) {
            $user->currentAccessToken()?->delete();
            
            // Log logout
            $this->auditLog->logLogout($user);
        }
        
        // Logout from session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('status', 'Logged out successfully');
    }
}
