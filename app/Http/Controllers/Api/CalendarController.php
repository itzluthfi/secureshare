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
        
        // 1. Milestones - spread across date range
        $milestones = Milestone::whereIn('project_id', $projectIds)
            ->with(['project', 'creator'])
            ->get();
            
        foreach ($milestones as $milestone) {
            $dates = $milestone->getDateRange();
            
            foreach ($dates as $date) {
                $events[] = [
                    'id' => 'milestone-' . $milestone->id,
                    'title' => $milestone->title,
                    'description' => $milestone->description,
                    'type' => $milestone->type,
                    'date' => $date,
                    'time' => $milestone->scheduled_time,
                    'project' => $milestone->project->name,
                    'project_id' => $milestone->project_id,
                    'is_completed' => $milestone->is_completed,
                    'category' => 'milestone',
                    'color' => $this->getEventColor($milestone->type),
                    'start_date' => $milestone->start_date ? $milestone->start_date->format('Y-m-d') : null,
                    'end_date' => $milestone->scheduled_date->format('Y-m-d'),
                ];
            }
        }
        
        // 2. Tasks - spread across date range
        $tasks = Task::whereIn('project_id', $projectIds)
            ->whereNotNull('deadline')
            ->with(['project', 'assignees'])
            ->get();
            
        foreach ($tasks as $task) {
            $dates = $task->getDateRange();
            
            foreach ($dates as $date) {
                $events[] = [
                    'id' => 'task-' . $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'type' => 'task',
                    'date' => $date,
                    'time' => null,
                    'project' => $task->project->name,
                    'project_id' => $task->project_id,
                    'is_completed' => $task->status === 'done',
                    'category' => 'task',
                    'color' => $task->status === 'done' ? '#10B981' : '#F59E0B',
                    'assignees' => $task->assignees->pluck('name')->join(', '),
                    'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                    'end_date' => $task->deadline->format('Y-m-d'),
                ];
            }
        }
        
        return response()->json($events);
    }
    
    /**
     * Get events for specific month
     * 
     * @OA\Get(
     *     path="/calendar/month/{year}/{month}",
     *     tags={"Calendar"},
     *     summary="Get calendar events for month",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="year", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="month", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Month events")
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
        
        $groupedEvents = [];
        
        // Milestones - show in all days between start_date and scheduled_date
        $milestones = Milestone::whereIn('project_id', $projectIds)
            ->where(function($query) use ($startDate, $endDate) {
                // Event overlaps with month if:
                // - start_date is null and scheduled_date is in range
                // - OR event's date range overlaps with month
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereNull('start_date')
                      ->whereBetween('scheduled_date', [$startDate, $endDate]);
                })
                ->orWhere(function($q) use ($startDate, $endDate) {
                    $q->whereNotNull('start_date')
                      ->where(function($q2) use ($startDate, $endDate) {
                          // Start date is before month end AND end date is after month start
                          $q2->where('start_date', '<=', $endDate)
                             ->where('scheduled_date', '>=', $startDate);
                      });
                });
            })
            ->with(['project'])
            ->get();
            
        foreach ($milestones as $milestone) {
            $dates = $milestone->getDateRange();
            
            foreach ($dates as $date) {
                // Only include dates within the current month
                $dateObj = Carbon::parse($date);
                if ($dateObj->between($startDate, $endDate)) {
                    if (!isset($groupedEvents[$date])) {
                        $groupedEvents[$date] = [];
                    }
                    
                    $groupedEvents[$date][] = [
                        'id' => 'milestone-' . $milestone->id,
                        'title' => $milestone->title,
                        'type' => $milestone->type,
                        'project' => $milestone->project->name,
                        'category' => 'milestone',
                        'color' => $this->getEventColor($milestone->type),
                        'is_completed' => $milestone->is_completed,
                        'start_date' => $milestone->start_date ? $milestone->start_date->format('Y-m-d') : null,
                        'end_date' => $milestone->scheduled_date->format('Y-m-d'),
                    ];
                }
            }
        }
        
        // Tasks - show in all days between start_date and deadline
        $tasks = Task::whereIn('project_id', $projectIds)
            ->whereNotNull('deadline')
            ->where(function($query) use ($startDate, $endDate) {
                // Task overlaps with month if:
                // - start_date is null and deadline is in range
                // - OR task's date range overlaps with month
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereNull('start_date')
                      ->whereBetween('deadline', [$startDate, $endDate]);
                })
                ->orWhere(function($q) use ($startDate, $endDate) {
                    $q->whereNotNull('start_date')
                      ->where(function($q2) use ($startDate, $endDate) {
                          // Start date is before month end AND deadline is after month start
                          $q2->where('start_date', '<=', $endDate)
                             ->where('deadline', '>=', $startDate);
                      });
                });
            })
            ->with(['project', 'assignees'])
            ->get();
            
        foreach ($tasks as $task) {
            $dates = $task->getDateRange();
            
            foreach ($dates as $date) {
                // Only include dates within the current month
                $dateObj = Carbon::parse($date);
                if ($dateObj->between($startDate, $endDate)) {
                    if (!isset($groupedEvents[$date])) {
                        $groupedEvents[$date] = [];
                    }
                    
                    $groupedEvents[$date][] = [
                        'id' => 'task-' . $task->id,
                        'title' => $task->title,
                        'type' => 'task',
                        'project' => $task->project->name,
                        'category' => 'task',
                        'color' => $task->status === 'done' ? '#10B981' : '#F59E0B',
                        'is_completed' => $task->status === 'done',
                        'assignees' => $task->assignees->pluck('name')->join(', '),
                        'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                        'end_date' => $task->deadline->format('Y-m-d'),
                    ];
                }
            }
        }
        
        // Count total events
        $totalEvents = 0;
        foreach ($groupedEvents as $events) {
            $totalEvents += count($events);
        }
        
        return response()->json([
            'month' => $month,
            'year' => $year,
            'events' => $groupedEvents,
            'total_events' => $totalEvents,
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
     *             required={"project_id","title","type","scheduled_date"},
     *             @OA\Property(property="project_id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="type", type="string", enum={"deadline", "meeting", "review", "launch", "milestone"}),
     *             @OA\Property(property="scheduled_date", type="string", format="date")
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
            'start_date' => 'nullable|date',
            'scheduled_date' => 'required|date|after_or_equal:start_date',
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
     * 
     * @OA\Put(
     *     path="/milestones/{id}",
     *     tags={"Calendar"},
     *     summary="Update milestone",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Milestone updated")
     * )
     */
    public function updateMilestone(Request $request, $id)
    {
        $milestone = Milestone::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:deadline,meeting,review,launch,milestone',
            'start_date' => 'nullable|date',
            'scheduled_date' => 'sometimes|date|after_or_equal:start_date',
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
     * 
     * @OA\Delete(
     *     path="/milestones/{id}",
     *     tags={"Calendar"},
     *     summary="Delete milestone",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Milestone deleted")
     * )
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
            'task' => '#F59E0B',
            default => '#6B7280',
        };
    }
}