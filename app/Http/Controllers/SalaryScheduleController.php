<?php

namespace App\Http\Controllers;

use App\Models\SalarySchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Imports\SalaryScheduleImport;
use App\Exports\SalaryScheduleExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Illuminate\Support\Facades\Storage;

class SalaryScheduleController extends Controller
{
    /**
     * Fetch all salary schedules.
     */
    public function getSalarySchedule(): JsonResponse
    {
        $salarySchedules = SalarySchedule::orderBy('salary_grade')->get();

        return response()->json($salarySchedules);
    }

    /**
     * Update salary schedules in bulk.
     */
    public function updateSalarySchedules(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            '*.id' => 'required|integer|exists:salary_schedules,id',
            '*.salary_grade' => 'required|integer',
            '*.step_1' => 'nullable|numeric',
            '*.step_2' => 'nullable|numeric',
            '*.step_3' => 'nullable|numeric',
            '*.step_4' => 'nullable|numeric',
            '*.step_5' => 'nullable|numeric',
            '*.step_6' => 'nullable|numeric',
            '*.step_7' => 'nullable|numeric',
            '*.step_8' => 'nullable|numeric',
        ]);

        try {
            foreach ($validatedData as $item) {
                $schedule = SalarySchedule::find($item['id']);
                $schedule->update($item);
            }

            return response()->json(['message' => 'Salary schedules updated successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update salary schedules.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import salary schedules from a CSV file.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            Excel::import(new SalaryScheduleImport, $request->file('file'), null, ExcelFormat::CSV);

            $updated = SalarySchedule::orderBy('salary_grade')->get();

            return response()->json([
                'message' => 'Salary schedules updated successfully via CSV upload.',
                'data' => $updated,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'CSV upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a CSV template for salary schedules.
     */
    public function exportTemplate()
    {
        $path = 'templates/salary_schedule_template.csv'; 

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'Template file not found.'], 404);
        }

        return response()->streamDownload(function () use ($path) {
            echo Storage::get($path);
        }, 'salary_schedule_template.csv', [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

}
