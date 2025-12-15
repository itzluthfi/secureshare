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
     * 
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Register new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
    /**
     * User login
     * 
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Authenticate user and return access token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abcd1234..."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Admin User"),
     *                 @OA\Property(property="email", type="string", example="admin@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
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
     * 
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logged out successfully")
     * )
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
     * Get authenticated user
     * 
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="Get current user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="User data")
     * )
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Get authenticated user with role permissions
     * 
     * @OA\Get(
     *     path="/auth/permissions",
     *     tags={"Authentication"},
     *     summary="Get user role and permissions",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User role and permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="permissions", type="object")
     *         )
     *     )
     * )
     */
    public function getPermissions(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'role' => $user->role,
            'permissions' => [
                'projects' => [
                    'create' => $user->isAdmin() || $user->isManager(),
                    'view_all' => $user->isAdmin(),
                    'manage_members' => $user->isAdmin() || $user->isManager(),
                ],
                'tasks' => [
                    'create' => true,
                    'update_any' => $user->isAdmin(),
                    'update_assigned' => true,
                    'delete' => $user->isAdmin() || $user->isManager(),
                ],
                'documents' => [
                    'upload' => true,
                    'download' => true,
                    'delete' => $user->isAdmin() || $user->isManager(),
                ],
                'milestones' => [
                    'create' => $user->isAdmin() || $user->isManager(),
                    'update' => $user->isAdmin() || $user->isManager(),
                    'delete' => $user->isAdmin() || $user->isManager(),
                ],
                'users' => [
                    'view_all' => $user->isAdmin(),
                    'create' => $user->isAdmin(),
                    'update' => $user->isAdmin(),
                    'delete' => $user->isAdmin(),
                ],
            ],
        ]);
    }
    
    /**
     * Forgot password
     * 
     * @OA\Post(
     *     path="/auth/forgot-password",
     *     tags={"Authentication"},
     *     summary="Request password reset",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reset link sent")
     * )
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
     * 
     * @OA\Post(
     *     path="/auth/reset-password",
     *     tags={"Authentication"},
     *     summary="Reset password with token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","email","password","password_confirmation"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successfully")
     * )
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
