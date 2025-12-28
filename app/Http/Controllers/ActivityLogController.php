<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(50)->through(fn($log) => [
            'id' => $log->id,
            'user_name' => $log->user_name,
            'action' => $log->action,
            'model_type' => $log->model_type,
            'model_id' => $log->model_id,
            'description' => $log->description,
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at->format('d/m/Y H:i:s'),
        ]);

        $actions = ActivityLog::distinct()->pluck('action');
        $modelTypes = ActivityLog::distinct()->whereNotNull('model_type')->pluck('model_type');

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'modelTypes' => $modelTypes,
            'filters' => $request->only(['action', 'user_id', 'model_type', 'date']),
        ]);
    }

    public function latest()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'user_name' => $log->user_name,
                'action' => $log->action,
                'description' => $log->description,
                'created_at' => $log->created_at->format('d/m/Y H:i:s'),
            ]);

        return response()->json($logs);
    }
}
