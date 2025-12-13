<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_MEMBER, // Default role
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->auditLog->log('register', "New user registered: {$user->name}", 'App\Models\User', $user->id);

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        \Log::info('=== LOGIN ATTEMPT ===');
        \Log::info('Email: ' . $request->email);
        \Log::info('IP: ' . $request->ip());
        
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                \Log::warning('User not found: ' . $request->email);
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            

            if (!Hash::check($request->password, $user->password)) {
                \Log::warning('Password mismatch for user: ' . $user->email);
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            

            if (!$user->is_active) {
                \Log::warning('Inactive user login attempt: ' . $user->email);
                return response()->json([
                    'message' => 'Your account has been deactivated. Please contact administrator.',
                ], 403);
            }


            // Revoke old tokens
            $user->tokens()->delete();
            \Log::info('Old tokens revoked');

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;
            \Log::info('New token created: ' . substr($token, 0, 20) . '...');

            $this->auditLog->logLogin($user);

            \Log::info('=== LOGIN SUCCESS ===');
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('=== LOGIN ERROR ===');
            \Log::error('Error: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        $this->auditLog->logLogout($user);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email']);
        }

        return response()->json(['message' => 'Unable to send reset link'], 500);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                $this->auditLog->log('password_reset', "Password reset for user: {$user->name}", 'App\Models\User', $user->id);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset successfully']);
        }

        return response()->json(['message' => 'Failed to reset password'], 500);
    }
}
