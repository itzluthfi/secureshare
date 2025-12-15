<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs (Admin only)
     * 
     * @OA\Get(
     *     path="/audit-logs",
     *     tags={"Audit Logs"},
     *     summary="List audit logs",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="action", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Audit logs list")
     * )
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
     * 
     * @OA\Get(
     *     path="/audit-logs/{id}",
     *     tags={"Audit Logs"},
     *     summary="Get audit log details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Log details")
     * )
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return response()->json($log);
    }

    /**
     * Export audit logs as CSV (Admin only)
     * 
     * @OA\Get(
     *     path="/audit-logs/export",
     *     tags={"Audit Logs"},
     *     summary="Export audit logs to CSV",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="CSV download")
     * )
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
