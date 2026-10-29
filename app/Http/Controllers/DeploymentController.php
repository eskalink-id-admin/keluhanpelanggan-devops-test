<?php

namespace App\Http\Controllers;

use App\Support\ReleaseInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DeploymentController extends Controller
{
    public function version(): JsonResponse
    {
        return response()->json(ReleaseInfo::current())->header('Cache-Control', 'no-store');
    }

    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok'])->header('Cache-Control', 'no-store');
    }

    public function ready(): JsonResponse
    {
        try {
            DB::select('SELECT 1');
            $database = Schema::hasTable('users') && Schema::hasTable('deployment_notes');
        } catch (Throwable) {
            $database = false;
        }

        $checks = [
            'database' => $database,
            'storage' => is_writable(storage_path('framework/views')) && is_writable(storage_path('logs')),
            'release' => ! config('release.simulate_failure'),
        ];
        $ready = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $checks,
            'release' => ReleaseInfo::current(),
        ], $ready ? 200 : 503)->header('Cache-Control', 'no-store');
    }
}
