<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataCleaningController extends Controller
{
    /**
     * Halaman Data Cleaning — menampilkan hasil cleaning dari Python
     */
    public function index()
    {
        // Load cleaning report from database
        $report = DB::table('cleaning_reports')
            ->orderBy('id', 'desc')
            ->first();

        $cleaningReport = $report ? json_decode($report->report_data, true) : null;

        return view('data-cleaning', compact('cleaningReport'));
    }

    /**
     * API: Get cleaning details per column
     */
    public function getCleaningDetails()
    {
        $report = DB::table('cleaning_reports')
            ->orderBy('id', 'desc')
            ->first();

        if (!$report) {
            return response()->json(['error' => 'No cleaning report found'], 404);
        }

        return response()->json(json_decode($report->report_data, true));
    }
}
