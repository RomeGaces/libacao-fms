<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ObjectExpenditure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectExpenditureController extends Controller
{
    public function index()
    {
        $items = ObjectExpenditure::with('group')->latest()->get();

        return response()->json([
            'message' => 'Object expenditures retrieved successfully.',
            'data'    => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id'           => 'required|exists:group_object_expenditures,id',
            'object_expenditure' => 'required|string|max:255',
            'account_code'       => 'required|string|max:50',
        ]);

        $existing = ObjectExpenditure::withTrashed()
            ->where('object_expenditure', $validated['object_expenditure'])
            ->where('account_code', $validated['account_code'])
            ->where('group_id', $validated['group_id'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update($validated);

                DB::table('audit_logs')->insert([
                    'auditable_id'   => $existing->id,
                    'auditable_type' => ObjectExpenditure::class,
                    'changes'        => json_encode(['from' => '[soft-deleted]', 'to' => $validated]),
                    'remarks'        => 'Restored soft-deleted object expenditure',
                    'updated_by'     => auth()->user()->employee_id ?? null,
                    'updated_at'     => now(),
                ]);

                return response()->json([
                    'message' => 'Object expenditure restored successfully.',
                    'data'    => $existing,
                ]);
            }

            return response()->json([
                'message' => 'Object expenditure already exists.',
                'data'    => $existing,
            ], 409);
        }

        $item = ObjectExpenditure::create($validated);

        DB::table('audit_logs')->insert([
            'auditable_id'   => $item->id,
            'auditable_type' => ObjectExpenditure::class,
            'changes'        => json_encode(['from' => null, 'to' => $validated]),
            'remarks'        => 'Created new object expenditure',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        return response()->json([
            'message' => 'Object expenditure created successfully.',
            'data'    => $item,
        ]);
    }

    public function show($id)
    {
        $item = ObjectExpenditure::withTrashed()->with('group')->findOrFail($id);

        return response()->json([
            'message' => 'Object expenditure retrieved successfully.',
            'data'    => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = ObjectExpenditure::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'group_id'           => 'required|exists:group_object_expenditures,id',
            'object_expenditure' => 'required|string|max:255',
            'account_code'       => 'required|string|max:50',
        ]);

        $original = $item->only(['group_id', 'object_expenditure', 'account_code']);

        $item->update($validated);

        DB::table('audit_logs')->insert([
            'auditable_id'   => $item->id,
            'auditable_type' => ObjectExpenditure::class,
            'changes'        => json_encode(['from' => $original, 'to' => $validated]),
            'remarks'        => 'Updated object expenditure info',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        return response()->json([
            'message' => 'Object expenditure updated successfully.',
            'data'    => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = ObjectExpenditure::withTrashed()->findOrFail($id);

        $original = $item->only(['group_id', 'object_expenditure', 'account_code']);

        DB::table('audit_logs')->insert([
            'auditable_id'   => $item->id,
            'auditable_type' => ObjectExpenditure::class,
            'changes'        => json_encode([
                'from' => $original,
                'to'   => [
                    'group_id'           => '[deleted]',
                    'object_expenditure' => '[deleted]',
                    'account_code'       => '[deleted]',
                ],
            ]),
            'remarks'        => 'Deleted object expenditure record',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        $item->delete();

        return response()->noContent();
    }
}