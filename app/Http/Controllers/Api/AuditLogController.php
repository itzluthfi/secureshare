<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs (Admin only)
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->when($request->user_id, function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            })
            ->when($request->action, function ($q) use ($request) {
                $q->where('action', $request->action);
            })
            ->when($request->date_from, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($query);
    }

    /**
     * Display the specified audit log
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return response()->json($log);
    }

    /**
     * Export audit logs as CSV (Admin only)
     */
    public function export(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->date_from, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "ID,User,Action,Description,IP Address,Created At\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->user ? $log->user->name : 'System',
                $log->action,
                str_replace(["\n", "\r", ","], [" ", " ", ";"], $log->description),
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');
    }
}
