<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Get all calendar events
     * 
     * @OA\Get(
     *     path="/calendar/events",
     *     tags={"Calendar"},
     *     summary="Get all calendar events",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Events list")
     * )
     */
    public function getEvents(Request $request)
    {
        $user = $request->user();
        
        // Get user's projects (only accepted members)
        $projectIds = $user->projects()
            ->wherePivot('status', 'accepted')
            ->pluck('projects.id')
            ->toArray();
        
        if (empty($projectIds)) {
            return response()->json([]);
        }
        
        $events = [];
        
        // 1. Milestones
        $milestones = Milestone::whereIn('project_id', $projectIds)
            ->with(['project', 'creator'])
            ->get();
            
        foreach ($milestones as $milestone) {
            $events[] = [
                'id' => 'milestone-' . $milestone->id,
                'title' => $milestone->title,
                'description' => $milestone->description,
                'type' => $milestone->type,
                'date' => $milestone->scheduled_date->format('Y-m-d'),
                'time' => $milestone->scheduled_time,
                'project' => $milestone->project->name,
                'project_id' => $milestone->project_id,
                'is_completed' => $milestone->is_completed,
                'category' => 'milestone',
                'color' => $this->getEventColor($milestone->type),
            ];
        }
        
        // 2. Tasks with deadlines
        $tasks = Task::whereIn('project_id', $projectIds)
            ->whereNotNull('deadline')
            ->with(['project', 'assignee'])
            ->get();
            
        foreach ($tasks as $task) {
            $events[] = [
                'id' => 'task-' . $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'type' => 'task',
                'date' => $task->deadline,
                'time' => null,
                'project' => $task->project->name,
                'project_id' => $task->project_id,
                'is_completed' => $task->status === 'done',
                'category' => 'task',
                'color' => $task->status === 'done' ? '#10B981' : '#F59E0B',
                'assignee' => $task->assignee ? $task->assignee->name : null,
            ];
        }
        
        // 3. Projects (created date as reference)
        $projects = Project::whereIn('id', $projectIds)
            ->with('creator')
            ->get();
            
        foreach ($projects as $project) {
            $events[] = [
                'id' => 'project-' . $project->id,
                'title' => $project->name . ' (Created)',
                'description' => $project->description,
                'type' => 'project',
                'date' => $project->created_at->format('Y-m-d'),
                'time' => null,
                'project' => $project->name,
                'project_id' => $project->id,
                'category' => 'project',
                'color' => '#4F7FFF',
            ];
        }
        
        return response()->json($events);
    }
    
    /**
     * Get events for specific month
     * 
     * @OA\Get(
     *     path="/calendar/month/{year}/{month}",
     *     tags={"Calendar"},
     *     summary="Get monthly events",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="year", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="month", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Monthly events")
     * )
     */
    public function getMonthView(Request $request, $year, $month)
    {
        $user = $request->user();
        $projectIds = $user->projects()
            ->wherePivot('status', 'accepted')
            ->pluck('projects.id')
            ->toArray();
        
        if (empty($projectIds)) {
            return response()->json([
                'month' => $month,
                'year' => $year,
                'events' => [],
                'total_events' => 0,
            ]);
        }
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $events = [];
        
        // Milestones in this month
        $milestones = Milestone::whereIn('project_id', $projectIds)
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->with(['project'])
            ->get();
            
        foreach ($milestones as $milestone) {
            $events[] = [
                'date' => $milestone->scheduled_date->format('Y-m-d'),
                'title' => $milestone->title,
                'type' => $milestone->type,
                'project' => $milestone->project->name,
                'category' => 'milestone',
                'color' => $this->getEventColor($milestone->type),
            ];
        }
        
        // Tasks due in this month
        $tasks = Task::whereIn('project_id', $projectIds)
            ->whereBetween('deadline', [$startDate, $endDate])
            ->with(['project'])
            ->get();
            
        foreach ($tasks as $task) {
            $events[] = [
                'date' => $task->deadline,
                'title' => $task->title,
                'type' => 'task',
                'project' => $task->project->name,
                'category' => 'task',
                'color' => $task->status === 'done' ? '#10B981' : '#F59E0B',
            ];
        }
        
        // Group events by date
        $groupedEvents = collect($events)->groupBy('date')->toArray();
        
        return response()->json([
            'month' => $month,
            'year' => $year,
            'events' => $groupedEvents,
            'total_events' => count($events),
        ]);
    }
    
    /**
     * Create milestone
     * 
     * @OA\Post(
     *     path="/milestones",
     *     tags={"Calendar"},
     *     summary="Create milestone",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"project_id", "title", "type", "scheduled_date"},
     *             @OA\Property(property="project_id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="type", type="string", enum={"deadline", "meeting", "review", "launch", "milestone"}),
     *             @OA\Property(property="scheduled_date", type="string", format="date"),
     *             @OA\Property(property="scheduled_time", type="string", format="time")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Milestone created")
     * )
     */
    public function storeMilestone(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:deadline,meeting,review,launch,milestone',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
        ]);
        
        $milestone = Milestone::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);
        
        return response()->json([
            'message' => 'Milestone created successfully',
            'milestone' => $milestone->load(['project', 'creator']),
        ], 201);
    }
    
    /**
     * Update milestone
     */
    public function updateMilestone(Request $request, $id)
    {
        $milestone = Milestone::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:deadline,meeting,review,launch,milestone',
            'scheduled_date' => 'sometimes|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'is_completed' => 'sometimes|boolean',
        ]);
        
        $milestone->update($validated);
        
        return response()->json([
            'message' => 'Milestone updated successfully',
            'milestone' => $milestone->fresh()->load(['project', 'creator']),
        ]);
    }
    
    /**
     * Delete milestone
     */
    public function destroyMilestone($id)
    {
        $milestone = Milestone::findOrFail($id);
        $milestone->delete();
        
        return response()->json(['message' => 'Milestone deleted successfully']);
    }
    
    /**
     * Get event color based on type
     */
    private function getEventColor($type)
    {
        return match($type) {
            'deadline' => '#EF4444',
            'meeting' => '#8B5CF6',
            'review' => '#F59E0B',
            'launch' => '#10B981',
            'milestone' => '#4F7FFF',
            default => '#6B7280',
        };
    }
}
