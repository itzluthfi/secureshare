<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Document;
use App\Models\Task;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/search",
     *     tags={"Search"},
     *     summary="Global search",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="q", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Search results")
     * )
     */
    public function index(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $user = $request->user();
        
        // Projects: user must be a member or admin
        $projectsQuery = Project::query()
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
            
        if (!$user->isAdmin()) {
            $projectsQuery->whereHas('members', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $projects = $projectsQuery->take(5)->get()->map(function($item) {
            return [
                'type' => 'project',
                'id' => $item->encrypted_id,
                'title' => $item->name,
                'subtitle' => 'Project',
                'url' => "/projects/{$item->encrypted_id}",
                'icon' => 'fa-folder'
            ];
        });
        
        // Documents: user must have access to project
        $documentsQuery = Document::query()
            ->where('name', 'like', "%{$query}%");
            
        if (!$user->isAdmin()) {
            $documentsQuery->whereHas('project.members', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $documents = $documentsQuery->with('project')->take(5)->get()->map(function($item) {
            return [
                'type' => 'document',
                'id' => $item->encrypted_id,
                'title' => $item->name,
                'subtitle' => 'Document in ' . ($item->project->name ?? 'Unknown'),
                'url' => "/documents/{$item->encrypted_id}",
                'icon' => 'fa-file-alt'
            ];
        });
        
        // Tasks: user must have access to project
        $tasksQuery = Task::query()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
            
        if (!$user->isAdmin()) {
            $tasksQuery->whereHas('project.members', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $tasks = $tasksQuery->with('project')->take(5)->get()->map(function($item) {
            // Link to Project's task tab
            $url = "/projects/{$item->project->encrypted_id}?tab=tasks&task={$item->id}";
            
            return [
                'type' => 'task',
                'id' => $item->encrypted_id, // Though we link to project, having this is good
                'title' => $item->title,
                'subtitle' => 'Task in ' . ($item->project->name ?? 'Unknown'),
                'url' => $url,
                'icon' => 'fa-tasks',
                'status' => $item->status // Extra info
            ];
        });
        
        return response()->json([
            'projects' => $projects,
            'documents' => $documents,
            'tasks' => $tasks
        ]);
    }
}
