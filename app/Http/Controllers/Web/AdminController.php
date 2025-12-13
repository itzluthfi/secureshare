<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users(Request $request)
    {
        // Auth will be checked via JavaScript
        return view('admin.users');
    }

    public function auditLogs(Request $request)
    {
        // Auth will be checked via JavaScript
        return view('admin.audit-logs');
    }
}
