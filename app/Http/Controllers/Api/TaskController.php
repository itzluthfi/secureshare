<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use App\Models\Notification;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;
    
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    /**
     * List project tasks
     * 
     * @OA\Get(
     *     path="/projects/{projectId}/tasks",
     *     tags={"Tasks"},
     *     summary="List project tasks",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Tasks list")
     * )
     */
    public function index(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('view', $project);

        $tasks = Task::where('project_id', $projectId)
            ->with(['assignee', 'creator'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->assigned_to, function ($query) use ($request) {
                $query->where('assigned_to', $request->assigned_to);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tasks);
    }

    /**
     * Create new task
     * 
     * @OA\Post(
     *     path="/projects/{projectId}/tasks",
     *     tags={"Tasks"},
     *     summary="Create new task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"todo", "in_progress", "done"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
     *             @OA\Property(property="assigned_to", type="integer"),
     *             @OA\Property(property="deadline", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Task created")
     * )
     */
    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('view', $project);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $task = Task::create([
            'project_id' => $projectId,
            'title' => $request->title,
            'description' => $request->description,
            'status' => Task::STATUS_TODO,
            'priority' => $request->priority ?? 'medium',
            'assigned_to' => $request->assigned_to,
            'created_by' => $request->user()->id,
            'deadline' => $request->deadline,
        ]);

        // Create notification for assigned user
        if ($request->assigned_to) {
            Notification::create([
                'user_id' => $request->assigned_to,
                'type' => 'task_assigned',
                'title' => 'New Task Assigned',
                'message' => "You have been assigned task: {$task->title}",
                'data' => ['task_id' => $task->id, 'project_id' => $projectId],
            ]);
        }

        $this->auditLog->logCreate($task, "Task created: {$task->title}");

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load(['assignee', 'creator']),
        ], 201);
    }

    /**
     * Get task details
     * 
     * @OA\Get(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Get task details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task details")
     * )
     */
    public function show(Request $request, $id)
    {
        $task = Task::with(['assignee', 'creator', 'project'])->findOrFail($id);
        $this->authorize('view', $task);

        return response()->json($task);
    }

    /**
     * Update task
     * 
     * @OA\Put(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Update task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="priority", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Task updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $oldValues = $task->toArray();

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,done',
            'priority' => 'sometimes|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $task->update($request->only(['title', 'description', 'status', 'priority', 'assigned_to', 'deadline']));

        $this->auditLog->logUpdate($task, $oldValues, "Task updated: {$task->title}");

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->load(['assignee', 'creator']),
        ]);
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        $this->auditLog->log(
            'task_status_update',
            "Task '{$task->title}' status changed from {$oldStatus} to {$request->status}",
            'App\Models\Task',
            $task->id
        );

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Delete task
     * 
     * @OA\Delete(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Delete task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task deleted")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);

        $taskTitle = $task->title;
        
        $this->auditLog->logDelete($task, "Task deleted: {$taskTitle}");
        
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}
