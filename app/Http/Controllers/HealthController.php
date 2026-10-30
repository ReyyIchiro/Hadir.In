<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function show(): JsonResponse
    {
        try {
            DB::select('select 1');
        } catch (Throwable) {
            return response()->json([
                'status' => 'error',
                'database' => 'error',
            ], 503);
        }

        return response()->json([
            'status' => 'ok',
            'database' => 'ok',
        ]);
    }
}
