<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnnualBudget;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnualBudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AnnualBudget::orderBy('year', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     * If a record for the year exists and is soft-deleted, it will be restored.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|digits:4',
            'annual_budget' => 'required|numeric|min:0',
        ]);

        // Include the creator (server-side; do not trust client)
        $creatorId = Auth::id();

        // Find a budget for the given year, including any soft-deleted ones.
        $budget = AnnualBudget::withTrashed()
            ->where('year', $validated['year'])
            ->first();

        if ($budget) {
            // If it exists and is soft-deleted, restore and update amount.
            if ($budget->trashed()) {
                $budget->restore();

                // Keep original created_by if present; otherwise, set to current user
                if (is_null($budget->created_by)) {
                    $budget->created_by = $creatorId;
                }

                $budget->annual_budget = $validated['annual_budget'];
                $budget->save();

                DB::table('audit_logs')->insert([
                    'auditable_id'   => $budget->id,
                    'auditable_type' => AnnualBudget::class,
                    'changes'        => json_encode(['from' => '[soft-deleted]', 'to' => $validated]),
                    'remarks'        => 'Restored soft-deleted annual budget',
                    'updated_by'     => auth()->user()->employee_id ?? null,
                    'updated_at'     => now(),
                ]);

                return response()->json($budget, Response::HTTP_OK);
            }

            // Active duplicate
            return response()->json([
                'message' => 'The year has already been taken.',
                'errors'  => ['year' => ['The year has already been taken.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create new
        $newBudget = AnnualBudget::create([
            'year'          => $validated['year'],
            'annual_budget' => $validated['annual_budget'],
            'created_by'    => $creatorId,
        ]);

        DB::table('audit_logs')->insert([
            'auditable_id'   => $newBudget->id,
            'auditable_type' => AnnualBudget::class,
            'changes'        => json_encode(['from' => null, 'to' => $validated]),
            'remarks'        => 'Created new annual budget',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        return response()->json($newBudget, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(AnnualBudget $annualBudget)
    {
        return $annualBudget;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnnualBudget $annualBudget)
    {
        $validated = $request->validate([
            'year' => 'required|integer|digits:4|unique:annual_budgets,year,' . $annualBudget->id,
            'annual_budget' => 'required|numeric|min:0',
        ]);

        $original = $annualBudget->only(['year', 'annual_budget']);

        $annualBudget->update($validated);

        DB::table('audit_logs')->insert([
            'auditable_id'   => $annualBudget->id,
            'auditable_type' => AnnualBudget::class,
            'changes'        => json_encode(['from' => $original, 'to' => $validated]),
            'remarks'        => 'Updated annual budget info',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        return response()->json($annualBudget);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnnualBudget $annualBudget)
    {
        $original = $annualBudget->only(['year', 'annual_budget']);

        DB::table('audit_logs')->insert([
            'auditable_id'   => $annualBudget->id,
            'auditable_type' => AnnualBudget::class,
            'changes'        => json_encode([
                'from' => $original,
                'to'   => [
                    'year'          => '[deleted]',
                    'annual_budget' => '[deleted]',
                ],
            ]),
            'remarks'        => 'Deleted annual budget record',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        $annualBudget->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}