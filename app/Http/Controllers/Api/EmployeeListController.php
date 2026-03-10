<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeStepAccess;
use App\Models\InternalStep;
use App\Models\Step;
use App\Models\OfficeCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeListController extends Controller
{
    /**
     * Display a listing of employees with their step access
     */
    public function index(Request $request): JsonResponse
    {
        $officeId = $request->input('office_id');
        $setId = $request->input('set_id');

        if (!$officeId || !$setId) {
            return response()->json([
                'message' => 'office_id and set_id are required'
            ], 400);
        }

        // Get the office code description to match with department
        $officeCode = OfficeCode::find($officeId);
        
        if (!$officeCode) {
            return response()->json([
                'message' => 'Office code not found'
            ], 404);
        }

        // Get internal steps for this office and set
        $internalSteps = InternalStep::whereHas('step', function($q) use ($officeId, $setId) {
            $q->where('office_code_id', $officeId)
              ->where('set_id', $setId);
        })
        ->with('step')
        ->orderBy('id')
        ->get();

        // Get employees from the same department
        // Match office_code.description with department.department_name
        $query = Employee::with(['stepAccess.internalStep', 'department'])
            ->whereHas('department', function($q) use ($officeCode) {
                $q->where('department_name', $officeCode->description);
            });

        // Filter by search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('agency_employee_no', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(10);

        // Transform employees to include access for each internal step
        $employees->getCollection()->transform(function ($employee) use ($internalSteps) {
            $accessMap = [];
            
            foreach ($internalSteps as $step) {
                // Check if employee has specific access record
                $access = $employee->stepAccess->firstWhere('internal_step_id', $step->id);
                
                // Default to true if no record exists, otherwise use the has_access value
                $accessMap[$step->id] = $access ? $access->has_access : true;
            }
            
            $employee->access_map = $accessMap;
            return $employee;
        });

        return response()->json([
            'employees' => $employees,
            'internal_steps' => $internalSteps
        ]);
    }

    /**
     * Display the specified employee
     */
    public function show($id): JsonResponse
    {
        $employee = Employee::with([
            'stepAccess.internalStep',
            'department',
            'familyMembers',
            'educations',
            'eligibilities',
            'workExperiences',
            'voluntaryWorks',
            'trainings',
            'otherInfos',
            'references'
        ])->findOrFail($id);

        return response()->json($employee);
    }

    /**
     * Update employee step access
     */
    public function updateStepAccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'internal_step_id' => 'required|exists:internal_steps,id',
            'has_access' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            EmployeeStepAccess::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'internal_step_id' => $validated['internal_step_id'],
                ],
                [
                    'has_access' => $validated['has_access']
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Step access updated successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update step access',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee's accessible internal steps
     * This will be used in ForApproval.vue to filter tabs
     */
    public function getEmployeeAccessibleSteps(Request $request): JsonResponse
    {
        $employeeId = $request->input('employee_id');
        $officeId = $request->input('office_id');
        $setId = $request->input('set_id');

        if (!$employeeId || !$officeId || !$setId) {
            return response()->json([
                'message' => 'employee_id, office_id and set_id are required'
            ], 400);
        }

        // Get all internal steps for this office and set
        $internalSteps = InternalStep::whereHas('step', function($q) use ($officeId, $setId) {
            $q->where('office_code_id', $officeId)
              ->where('set_id', $setId);
        })
        ->with('step')
        ->orderBy('id')
        ->get();

        // Get employee's access records
        $accessRecords = EmployeeStepAccess::where('employee_id', $employeeId)
            ->whereIn('internal_step_id', $internalSteps->pluck('id'))
            ->get()
            ->keyBy('internal_step_id');

        // Filter accessible steps (default true if no record)
        $accessibleSteps = $internalSteps->filter(function($step) use ($accessRecords) {
            $access = $accessRecords->get($step->id);
            return $access ? $access->has_access : true;
        })->values();

        return response()->json($accessibleSteps);
    }

    /**
     * Debug endpoint to check data
     */
    public function debug(Request $request): JsonResponse
    {
        $officeId = $request->input('office_id');
        $officeCode = OfficeCode::find($officeId);
        
        return response()->json([
            'office_id' => $officeId,
            'office_code' => $officeCode,
            'total_employees' => Employee::count(),
            'employees_with_dept' => Employee::whereNotNull('department_id')->count(),
            'sample_employee_with_dept' => Employee::with('department')->first(),
            'matching_employees' => Employee::whereHas('department', function($q) use ($officeCode) {
                if ($officeCode) {
                    $q->where('department_name', $officeCode->description);
                }
            })->count(),
        ]);
    }
}