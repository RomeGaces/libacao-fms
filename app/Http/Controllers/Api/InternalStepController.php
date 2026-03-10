<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalStep;
use App\Models\Set;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalStepController extends Controller
{
    /**
     * Get a list of internal steps filtered by the parent step's office_code_id.
     *
     * @param int $officeCodeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByOfficeCodeId(int $officeCodeId): JsonResponse
    {
        $internalSteps = InternalStep::with('step.officeCode')
            ->whereHas('step', function ($query) use ($officeCodeId) {
                $query->where('office_code_id', $officeCodeId);
            })
            ->get();

        return response()->json($internalSteps);
    }

    /**
     * Get a list of internal steps filtered by BOTH set_id and office_code_id.
     *
     * @param int $officeCodeId
     * @param int $setId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByOfficeAndSet(int $officeCodeId, int $setId): JsonResponse
    {
        $internalSteps = InternalStep::with('step.officeCode', 'step.set')
            ->whereHas('step', function ($query) use ($officeCodeId, $setId) {
                $query->where('office_code_id', $officeCodeId)
                      ->where('set_id', $setId);
            })
            ->get();

        return response()->json($internalSteps);
    }

    /**
     * Display a listing of all sets.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllSets(): JsonResponse
    {
        $sets = Set::orderBy('set_no')->get();

        return response()->json($sets);
    }
}