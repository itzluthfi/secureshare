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
     * Display a listing of users (Role-based filtering)
     * 
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="List users",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Users list")
     * )
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();
        
        // Role-based filtering
        if ($currentUser->isAdmin()) {
            // Admin can see ALL users
            $users = User::orderBy('name')->get();
        } 
        elseif ($currentUser->isManager()) {
            // Manager can see all members, but NOT admins
            $users = User::whereIn('role', ['manager', 'member'])
                ->orderBy('name')
                ->get();
        } 
        else {
            // Member can ONLY see users in same projects
            $projectIds = $currentUser->projects()->pluck('projects.id');
            
            $users = User::where(function($query) use ($projectIds, $currentUser) {
                $query->whereHas('projects', function($q) use ($projectIds) {
                    $q->whereIn('projects.id', $projectIds);
                })
                ->orWhereHas('createdProjects', function($q) use ($projectIds) {
                    $q->whereIn('projects.id', $projectIds);
                });
            })
            ->where('id', '!=', $currentUser->id) // Exclude self
            ->orderBy('name')
            ->get();
            
            // Always include self
            $users->prepend($currentUser);
        }
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user
     * 
     * @OA\Post(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Create new user",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="role", type="string", enum={"admin", "manager", "member"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created")
     * )
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
     * 
     * @OA\Get(
     *     path="/users/{user}",
     *     tags={"Users"},
     *     summary="Get user details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User details")
     * )
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified user
     * 
     * @OA\Put(
     *     path="/users/{user}",
     *     tags={"Users"},
     *     summary="Update user details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated")
     * )
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
     * 
     * @OA\Delete(
     *     path="/users/{user}",
     *     tags={"Users"},
     *     summary="Delete user",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User deleted")
     * )
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
     * 
     * @OA\Put(
     *     path="/users/{user}/role",
     *     tags={"Users"},
     *     summary="Assign user role",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"admin", "manager", "member"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Role updated")
     * )
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
