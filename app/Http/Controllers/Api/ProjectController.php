<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    /**
     * Display a listing of projects
     * 
     * @OA\Get(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="List all projects",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Projects list")
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Project::with(['creator', 'members']);

        // Admin sees all, others see only their projects
        if (!$user->isAdmin()) {
            $query->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $projects = $query->paginate(15);

        return response()->json($projects);
    }

    /**
     * Create new project
     * 
     * @OA\Post(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="Create new project",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="My Project"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Project created")
     * )
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $request->user()->id,
            'is_active' => true,
        ]);

       // Automatically add creator as owner with ACCEPTED status
$project->members()->attach($request->user()->id, [
    'role' => 'owner',
    'status' => 'accepted',
    'responded_at' => now(),
]);

        $this->auditLog->logCreate($project, "Project created: {$project->name}");

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project->load(['creator', 'members']),
        ], 201);
    }

    /**
     * Get project details
     * 
     * @OA\Get(
     *     path="/projects/{id}",
     *     tags={"Projects"},
     *     summary="Get project details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Project details")
     * )
     */
    public function show($id)
    {
        $project = Project::with(['creator', 'members', 'documents', 'tasks'])->findOrFail($id);
        
        $this->authorize('view', $project);

        return response()->json($project);
    }

    /**
     * Update project
     * 
     * @OA\Put(
     *     path="/projects/{id}",
     *     tags={"Projects"},
     *     summary="Update project",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Project updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        $oldValues = $project->toArray();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $project->update($request->only(['name', 'description', 'is_active']));

        $this->auditLog->logUpdate($project, $oldValues, "Project updated: {$project->name}");

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project,
        ]);
    }

    /**
     * Delete project
     * 
     * @OA\Delete(
     *     path="/projects/{id}",
     *     tags={"Projects"},
     *     summary="Delete project",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Project deleted")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('delete', $project);

        $projectName = $project->name;
        
        $this->auditLog->logDelete($project, "Project deleted: {$projectName}");
        
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ]);
    }

    /**
     * Add member to project
     */
    public function addMember(Request $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,manager,member',
        ]);

        if ($project->hasMember($request->user_id)) {
            return response()->json([
                'message' => 'User is already a member of this project',
            ], 400);
        }

        // Create pending invitation instead of direct add
        $project->members()->attach($request->user_id, [
            'role' => $request->role,
            'status' => 'pending', // Invitation is pending
        ]);

        $user = User::find($request->user_id);
        
        // Create notification for invited user
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'project_invitation',
            'title' => 'Project Invitation',
            'message' => "You have been invited to join project '{$project->name}' as {$request->role}. Please accept or decline.", 
            'data' => json_encode([
                'project_id' => $project->id,
                'project_name' => $project->name,
                'role' => $request->role,
                'inviter_id' => auth()->id(),
                'inviter_name' => auth()->user()->name,
                'action_required' => true, // Mark that action is needed
            ]),
        ]);
        
        $this->auditLog->log(
            'member_invited',
            "User {$user->name} invited to project {$project->name} as {$request->role}",
            'App\Models\Project',
            $project->id
        );

        return response()->json([
            'message' => 'Invitation sent successfully',
            'project' => $project->load('members'),
        ]);
    }

    /**
     * Remove member from project
     */
    public function removeMember(Request $request, Project $project, $userId)
    {
        $this->authorize('manageMembers', $project);

        if (!$project->hasMember($userId)) {
            return response()->json([
                'message' => 'User is not a member of this project',
            ], 404);
        }

        $project->removeMember($userId);

        $user = User::find($userId);
        $this->auditLog->log(
            'member_removed',
            "User {$user->name} removed from project {$project->name}",
            'App\Models\Project',
            $project->id
        );

        return response()->json([
            'message' => 'Member removed successfully',
        ]);
    }

    /**
     * Update member role
     */
    public function updateMemberRole(Request $request, Project $project, $userId)
    {
        $this->authorize('manageMembers', $project);

        $request->validate([
            'role' => 'required|in:owner,manager,member',
        ]);

        if (!$project->hasMember($userId)) {
            return response()->json([
                'message' => 'User is not a member of this project',
            ], 404);
        }

        $project->updateMemberRole($userId, $request->role);

        $user = User::find($userId);
        $this->auditLog->log(
            'member_role_updated',
            "User {$user->name} role updated to {$request->role} in project {$project->name}",
            'App\Models\Project',
            $project->id
        );

        return response()->json([
            'message' => 'Member role updated successfully',
            'project' => $project->load('members'),
        ]);
    }
    
    /**
     * Accept project invitation
     */
    public function acceptInvitation(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user has pending invitation
        $invitation = \DB::table('project_members')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
            
        if (!$invitation) {
            return response()->json(['message' => 'No pending invitation found'], 404);
        }
        
        // Update status to accepted
        \DB::table('project_members')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);
            
        // Mark notification as read
        \App\Models\Notification::where('user_id', $user->id)
            ->where('type', 'project_invitation')
            ->whereRaw("JSON_EXTRACT(data, '$.project_id') = ?", [$project->id])
            ->update(['read_at' => now()]);
        
        $this->auditLog->log(
            'invitation_accepted',
            "User {$user->name} accepted invitation to project {$project->name}",
            'App\Models\Project',
            $project->id
        );
        
        return response()->json([
            'message' => 'Invitation accepted successfully',
            'project' => $project->load('members'),
        ]);
    }
    
    /**
     * Decline project invitation
     */
    public function declineInvitation(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Check if user has pending invitation
        $invitation = \DB::table('project_members')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
            
        if (!$invitation) {
            return response()->json(['message' => 'No pending invitation found'], 404);
        }
        
        // Update status to declined
        \DB::table('project_members')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => 'declined',
                'responded_at' => now(),
            ]);
            
        // Mark notification as read
        \App\Models\Notification::where('user_id', $user->id)
            ->where('type', 'project_invitation')
            ->whereRaw("JSON_EXTRACT(data, '$.project_id') = ?", [$project->id])
            ->update(['read_at' => now()]);
        
        $this->auditLog->log(
            'invitation_declined',
            "User {$user->name} declined invitation to project {$project->name}",
            'App\Models\Project',
            $project->id
        );
        
        return response()->json(['message' => 'Invitation declined']);
    }
    /**
     * Get project activities
     */
    public function getActivities($projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('view', $project); // Any member can view activity
        
        // Fetch audit logs related to this project
        // Including direct project logs and related item logs (tasks, members, documents)
        $activities = \App\Models\AuditLog::where(function($query) use ($projectId) {
                // Direct project activities
                $query->where('model_type', 'App\\Models\\Project')
                      ->where('model_id', $projectId);
            })
            ->orWhere(function($query) use ($projectId) {
               // Include logs for tasks related to this project
                $query->where('model_type', 'App\\Models\\Task')
                      ->whereIn('model_id', function($q) use ($projectId) {
                          $q->select('id')->from('tasks')->where('project_id', $projectId);
                      });
            })
             ->orWhere(function($query) use ($projectId) {
                // Include logs for documents related to this project (if document has project_id)
                $query->where('model_type', 'App\\Models\\Document')
                      ->whereIn('model_id', function($q) use ($projectId) {
                          $q->select('id')->from('documents')->where('project_id', $projectId);
                      });
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
            
            // Transform for frontend
            $transformed = $activities->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action, // 'created', 'updated', 'member_added', etc.
                    'event' => $log->action,  // Alias for compatibility
                    'description' => $log->description, // The readable message (was details, but model says description)
                    'user' => $log->user,
                    'created_at' => $log->created_at,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $transformed
        ]);
    }
}
