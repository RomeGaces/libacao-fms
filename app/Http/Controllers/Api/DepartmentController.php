<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('id')->get();
        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|unique:departments,department_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $allCodes = Department::pluck('department_code')->toArray();
            $maxNumber = 0;

            foreach ($allCodes as $code) {
                if (preg_match('/LM(\d+)/', $code, $matches)) {
                    $num = (int)$matches[1];
                    if ($num > $maxNumber) $maxNumber = $num;
                }
            }

            $nextNumber = $maxNumber + 1;
            $departmentCode = 'LM' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $department = Department::create([
                'department_name' => $request->department_name,
                'department_code' => $departmentCode,
            ]);

            return response()->json($department, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Department $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|unique:departments,department_name,' . $department->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department->update([
            'department_name' => $request->department_name,
        ]);

        return response()->json($department);
    }

    public function destroy(Department $department)
    {
        $protectedCodes = ['LM001','LM005','LM006','LM007']; 

        if (in_array($department->department_code, $protectedCodes)) {
            return response()->json([
                'error' => 'This department cannot be deleted.'
            ], 403);
        }

        $department->delete();
        return response()->json(['message' => 'Department deleted successfully']);
    }

}
