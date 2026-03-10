<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:employee_id,name,email')
            ->orderBy('updated_at', 'desc');

        // Filter by auditable_type (model)
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        // Filter by updated_by (user employee_id)
        if ($request->filled('updated_by')) {
            $query->where('updated_by', $request->updated_by);
        }

        // Filter by date range - from
        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }

        // Filter by date range - to
        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        // Search by remarks or auditable_id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', "%{$search}%")
                  ->orWhere('auditable_id', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $auditLogs = $query->paginate($perPage);

        // Add formatted model name to each log
        $auditLogs->getCollection()->transform(function ($log) {
            $log->model_name = $this->formatModelName($log->auditable_type);
            return $log;
        });

        return response()->json($auditLogs);
    }

    /**
     * Display a specific audit log entry.
     */
    public function show($id): JsonResponse
    {
        $auditLog = AuditLog::with('user:employee_id,name,email')
            ->findOrFail($id);

        $auditLog->model_name = $this->formatModelName($auditLog->auditable_type);

        return response()->json($auditLog);
    }

    /**
     * Get all unique auditable types (models) that have audit logs.
     */
    public function getAuditableTypes(): JsonResponse
    {
        $types = AuditLog::select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        // Format the types to be more readable
        $formattedTypes = $types->map(function ($type) {
            return [
                'value' => $type,
                'label' => $this->formatModelName($type),
            ];
        });

        return response()->json($formattedTypes);
    }

    /**
     * Get all users who have made changes (appear in audit logs).
     */
    public function getAuditUsers(): JsonResponse
    {
        $users = AuditLog::join('users', 'audit_logs.updated_by', '=', 'users.employee_id')
            ->select('users.employee_id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        return response()->json($users);
    }

    /**
     * Helper method to format model class names to readable labels.
     */
    private function formatModelName($modelClass): string
    {
        // Extract just the class name from the full namespace
        $parts = explode('\\', $modelClass);
        $className = end($parts);

        // Convert from PascalCase to Title Case with spaces
        $formatted = preg_replace('/(?<!^)([A-Z])/', ' $1', $className);

        return $formatted;
    }
}