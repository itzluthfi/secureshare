<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        Log::info('TeamController@index accessed', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);
        
        // Auth will be checked via JavaScript
        return view('team');
    }
}
