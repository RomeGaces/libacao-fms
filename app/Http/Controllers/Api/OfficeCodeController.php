<?php

namespace App\Http\Controllers\Api;

use App\Models\OfficeCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfficeCodeRequest;
use App\Http\Requests\UpdateOfficeCodeRequest;
use Illuminate\Http\Response;


class OfficeCodeController extends Controller
{
    public function index()
    {
        return OfficeCode::latest()->paginate(10);
    }

    public function store(StoreOfficeCodeRequest $request)
    {
        $validated = $request->validated();

        $code = OfficeCode::create($validated);

        // Log creation in audit_logs
        \DB::table('audit_logs')->insert([
            'auditable_id'   => $code->id,
            'auditable_type' => OfficeCode::class,
            'changes'        => json_encode([
                'from' => null,
                'to'   => $code->only(['office_code', 'description']),
            ]),
            'remarks'        => 'Created new office code record',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        return response()->json($code, Response::HTTP_CREATED);
    }

    public function show(OfficeCode $officeCode)
    {
        return $officeCode;
    }

    public function update(UpdateOfficeCodeRequest $request, OfficeCode $officeCode)
    {
            // Capture original values
        $original = $officeCode->only(['office_code', 'description']);

        // Apply validated update
        $officeCode->update($request->validated());

        // Capture new values
        $updated = $officeCode->only(['office_code', 'description']);

        // Prepare audit log entry
        \DB::table('audit_logs')->insert([
            'auditable_id'   => $officeCode->id,
            'auditable_type' => OfficeCode::class,
            'changes'        => json_encode([
                'from' => $original,
                'to'   => $updated,
            ]),
            'remarks'        => 'Updated office code details',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        return response()->json([
            'message' => 'Office code updated and logged successfully.',
            'data'    => $officeCode,
        ]);
    }

    /**
     * Find an office code by its description and return its ID.
     *
     * @param  string  $description
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIdByDescription($description)
    {
        // Find the first office code matching the description
        // Using firstOrFail will automatically handle the 404 if not found
        $officeCode = OfficeCode::where('description', $description)->first();

        // If not found, return a 404 response
        if (!$officeCode) {
            return response()->json(['message' => 'Office code not found.'], Response::HTTP_NOT_FOUND);
        }

        // If found, return the ID
        return response()->json(['id' => $officeCode->id]);
    }

    
    public function destroy(OfficeCode $officeCode)
    {
        // Capture original values before deletion
        $original = $officeCode->only(['office_code', 'description']);

        // Log deletion in centralized audit_logs
        \DB::table('audit_logs')->insert([
            'auditable_id'   => $officeCode->id,
            'auditable_type' => OfficeCode::class,
            'changes'        => json_encode([
                'from' => $original,
                'to'   => [
                    'office_code' => '[deleted]',
                    'description' => '[deleted]',
                ],
            ]),
            'remarks'        => 'Deleted office code record',
            'updated_by'     => auth()->user()->employee_id ?? null,
            'updated_at'     => now(),
        ]);

        // Perform the delete
        $officeCode->delete();

        return response()->noContent(); // 204 No Content
    }
}
