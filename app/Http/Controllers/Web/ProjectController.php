<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        Log::info('ProjectController@index accessed', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);
        
        // Auth will be checked via JavaScript
        return view('projects.index');
    }

    public function show(Request $request, $id)
    {
        Log::info('ProjectController@show accessed', [
            'project_id' => $id,
            'ip' => $request->ip()
        ]);
        
        // Auth will be checked via JavaScript
        $project = Project::with(['creator', 'members', 'documents', 'tasks'])->findOrFail($id);
        return view('projects.show', compact('project'));
    }

    public function create(Request $request)
    {
        Log::info('ProjectController@create accessed', [
            'ip' => $request->ip()
        ]);
        
        // Auth will be checked via JavaScript
        return view('projects.create');
    }
}
