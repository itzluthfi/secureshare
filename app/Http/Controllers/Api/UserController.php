<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    /**
     * Display a listing of users (Admin only)
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->role, function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->paginate(15);

        return response()->json($users);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,manager,member',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        $this->auditLog->logCreate($user, "Admin created user: {$user->name}");

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $oldValues = $user->toArray();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->only(['name', 'email', 'is_active']);
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $this->auditLog->logUpdate($user, $oldValues, "Admin updated user: {$user->name}");

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $userName = $user->name;
        
        $this->auditLog->logDelete($user, "Admin deleted user: {$userName}");
        
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,manager,member',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        $this->auditLog->log(
            'role_change',
            "User {$user->name} role changed from {$oldRole} to {$request->role}",
            'App\Models\User',
            $user->id,
            ['role' => $oldRole],
            ['role' => $request->role]
        );

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user,
        ]);
    }
}
