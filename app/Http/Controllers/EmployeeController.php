<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with relationships.
     */
    public function index()
    {
        $employees = Employee::with([
            'familyMembers',
            'educations',
            'eligibilities',
            'workExperiences',
            'voluntaryWorks',
            'trainings',
            'otherInfos',
            'references',
            'department'
        ])->get();
        return response()->json($employees);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Employee fields
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'name_extension' => 'nullable|string|max:10',
            'birth_date' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'civil_status' => 'nullable|string|max:50',
            'citizenship' => 'nullable|string|max:100',
            'height' => 'nullable|string|max:10',
            'weight' => 'nullable|string|max:10',
            'blood_type' => 'nullable|string|max:5',
            'gsis_id_no' => 'nullable|string|max:50',
            'pagibig_id_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'sss_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
            'agency_employee_no' => 'nullable|string|max:50',
            'residential_address' => 'nullable|string|max:255',
            'residential_zip' => 'nullable|string|max:10',
            'permanent_address' => 'nullable|string|max:255',
            'permanent_zip' => 'nullable|string|max:10',
            'telephone_no' => 'nullable|string|max:50',
            'mobile_no' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'ctc_number' => 'nullable|string|max:50',
            'ctc_place_of_issuance' => 'nullable|string|max:100',
            'ctc_date_of_issuance' => 'nullable|date',

            // Nested Relations
            'family_members' => 'array',
            'educations' => 'array',
            'eligibilities' => 'array',
            'work_experiences' => 'array',
            'voluntary_works' => 'array',
            'trainings' => 'array',
            'other_infos' => 'array',
            'references' => 'array',

            'department_id' => 'nullable|exists:departments,id',
            'plantilla_id' => 'nullable|exists:plantillas,id'
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::create($validated);
            
            User::create([
                'gsis_id' => $employee->gsis_id_no,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'employee_id' => $employee->id,
                'password' => 'password123',
                'is_admin' => 0
            ]);

            // Save relations if provided
            if ($request->has('family_members')) {
                $employee->familyMembers()->createMany($request->family_members);
            }
            if ($request->has('educations')) {
                $employee->educations()->createMany($request->educations);
            }
            if ($request->has('eligibilities')) {
                $employee->eligibilities()->createMany($request->eligibilities);
            }
            if ($request->has('work_experiences')) {
                $employee->workExperiences()->createMany($request->work_experiences);
            }
            if ($request->has('voluntary_works')) {
                $employee->voluntaryWorks()->createMany($request->voluntary_works);
            }
            if ($request->has('trainings')) {
                $employee->trainings()->createMany($request->trainings);
            }
            if ($request->has('other_infos')) {
                $employee->otherInfos()->createMany($request->other_infos);
            }
            if ($request->has('references')) {
                $employee->references()->createMany($request->references);
            }

            DB::commit();

           

            return response()->json($employee->load([
                'familyMembers',
                'educations',
                'eligibilities',
                'workExperiences',
                'voluntaryWorks',
                'trainings',
                'otherInfos',
                'references'
            ]), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified employee with relationships.
     */
    public function show(Employee $employee)
    {
        return response()->json($employee->load([
            'familyMembers',
            'educations',
            'eligibilities',
            'workExperiences',
            'voluntaryWorks',
            'trainings',
            'otherInfos',
            'references'
        ]));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'name_extension' => 'nullable|string|max:10',
            'birth_date' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'civil_status' => 'nullable|string|max:50',
            'citizenship' => 'nullable|string|max:100',
            'height' => 'nullable|string|max:10',
            'weight' => 'nullable|string|max:10',
            'blood_type' => 'nullable|string|max:5',
            'gsis_id_no' => 'nullable|string|max:50',
            'pagibig_id_no' => 'nullable|string|max:50',
            'philhealth_no' => 'nullable|string|max:50',
            'sss_no' => 'nullable|string|max:50',
            'tin_no' => 'nullable|string|max:50',
            'agency_employee_no' => 'nullable|string|max:50',
            'residential_address' => 'nullable|string|max:255',
            'residential_zip' => 'nullable|string|max:10',
            'permanent_address' => 'nullable|string|max:255',
            'permanent_zip' => 'nullable|string|max:10',
            'telephone_no' => 'nullable|string|max:50',
            'mobile_no' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'ctc_number' => 'nullable|string|max:50',
            'ctc_place_of_issuance' => 'nullable|string|max:100',
            'ctc_date_of_issuance' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'plantilla_id' => 'nullable|exists:plantillas,id',
        ]);

        DB::beginTransaction();
        try {
            $employee->update($validated);

            // Replace relations if provided
            if ($request->has('family_members')) {
                $employee->familyMembers()->delete();
                $employee->familyMembers()->createMany($request->family_members);
            }
            if ($request->has('educations')) {
                $employee->educations()->delete();
                $employee->educations()->createMany($request->educations);
            }
            if ($request->has('eligibilities')) {
                $employee->eligibilities()->delete();
                $employee->eligibilities()->createMany($request->eligibilities);
            }
            if ($request->has('work_experiences')) {
                $employee->workExperiences()->delete();
                $employee->workExperiences()->createMany($request->work_experiences);
            }
            if ($request->has('voluntary_works')) {
                $employee->voluntaryWorks()->delete();
                $employee->voluntaryWorks()->createMany($request->voluntary_works);
            }
            if ($request->has('trainings')) {
                $employee->trainings()->delete();
                $employee->trainings()->createMany($request->trainings);
            }
            if ($request->has('other_infos')) {
                $employee->otherInfos()->delete();
                $employee->otherInfos()->createMany($request->other_infos);
            }
            if ($request->has('references')) {
                $employee->references()->delete();
                $employee->references()->createMany($request->references);
            }

            DB::commit();

            return response()->json($employee->load([
                'familyMembers',
                'educations',
                'eligibilities',
                'workExperiences',
                'voluntaryWorks',
                'trainings',
                'otherInfos',
                'references'
            ]));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(null, 204);
    }
}
