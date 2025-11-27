<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViolationList;
use Illuminate\Support\Facades\DB;

class DebugController extends Controller
{
    /**
     * Debug violation list data
     */
    public function violationListDebug()
    {
        $debug = [];

        // 1. Check if table exists
        $tableExists = DB::getSchemaBuilder()->hasTable('violation_lists');
        $debug['table_exists'] = $tableExists;

        // 2. Count records in violation_lists table
        $violationCount = ViolationList::count();
        $debug['violation_count'] = $violationCount;

        // 3. Get all violations from database
        $violations = ViolationList::all();
        $debug['violations'] = $violations->map(function($v) {
            return [
                'id' => $v->id,
                'title' => $v->title,
                'severity' => $v->severity,
                'category' => $v->category,
            ];
        })->toArray();

        // 4. Check database connection
        try {
            DB::connection()->getPdo();
            $debug['database_connected'] = true;
            $debug['database_name'] = DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            $debug['database_connected'] = false;
            $debug['database_error'] = $e->getMessage();
        }

        // 5. Raw SQL query result
        $rawResults = DB::select('SELECT COUNT(*) as count FROM violation_lists');
        $debug['raw_count'] = $rawResults[0]->count ?? 0;

        // 6. Check if seeder has been run
        $debug['seeder_status'] = $violationCount > 0 ? 'SEEDED ✓' : 'NOT SEEDED ✗';

        // 7. Sample violations (first 5)
        $debug['sample_violations'] = ViolationList::limit(5)->get()->toArray();

        // 8. Environment info
        $debug['environment'] = [
            'app_env' => env('APP_ENV'),
            'app_debug' => env('APP_DEBUG'),
            'db_driver' => env('DB_CONNECTION'),
            'db_host' => env('DB_HOST'),
        ];

        return response()->json($debug, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Test violation list API endpoint (same as used in modal)
     */
    public function testViolationListApi()
    {
        try {
            $violationTypes = ViolationList::select('id', 'title', 'severity', 'category', 'description')
                ->orderBy('severity')
                ->orderBy('title')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $violationTypes->count(),
                'violation_types' => $violationTypes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Display debug page in HTML
     */
    public function violationListDebugPage()
    {
        $debug = [];

        // 1. Check if table exists
        $tableExists = DB::getSchemaBuilder()->hasTable('violation_lists');
        $debug['table_exists'] = $tableExists;

        // 2. Count records
        $violationCount = ViolationList::count();
        $debug['violation_count'] = $violationCount;

        // 3. Get all violations
        $violations = ViolationList::orderBy('severity')->orderBy('title')->get();
        $debug['violations'] = $violations;

        // 4. Database info
        try {
            DB::connection()->getPdo();
            $debug['database_connected'] = true;
            $debug['database_name'] = DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            $debug['database_connected'] = false;
            $debug['database_error'] = $e->getMessage();
        }

        // 5. Environment
        $debug['environment'] = [
            'app_env' => env('APP_ENV'),
            'app_debug' => env('APP_DEBUG'),
            'db_driver' => env('DB_CONNECTION'),
            'db_host' => env('DB_HOST'),
        ];

        return view('debug.violation-list', compact('debug'));
    }
}
