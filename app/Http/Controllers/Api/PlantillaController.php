<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plantilla;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlantillaController extends Controller
{
    public function index()
    {
        $plantillas = Plantilla::with(['department', 'division'])->get();
        return response()->json($plantillas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plantilla_item_number'    => 'required|string|max:255|unique:plantillas,plantilla_item_number',
            'salary_grade'             => 'required|integer|exists:salary_schedules,salary_grade',
            'step'                     => 'required|integer|min:1|max:8',
            'eligibility_requirement'  => 'required|string',
            'educational_requirement'  => 'required|string',
            'department_id'            => 'required|integer|exists:departments,id',
            'division_id'              => 'required|integer|exists:divisions,id',
            'experience'               => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $division = Division::find($request->division_id);
        if (!$division || $division->department_id != $request->department_id) {
            return response()->json([
                'error' => 'The selected division does not belong to the chosen department.'
            ], 422);
        }

        $plantilla = Plantilla::create([
            'plantilla_item_number'    => $request->plantilla_item_number,
            'salary_grade'             => $request->salary_grade,
            'step'                     => $request->step,
            'eligibility_requirement'  => $request->eligibility_requirement,
            'educational_requirement'  => $request->educational_requirement,
            'department_id'            => $request->department_id,
            'division_id'              => $request->division_id,
            'experience'               => $request->experience,
        ]);

        return response()->json([
            'message' => 'Plantilla position added successfully.',
            'data' => $plantilla->load('department', 'division')
        ], 201);
    }

    public function show(Plantilla $plantilla)
    {
        $plantilla->load(['department', 'division']);
        return response()->json($plantilla);
    }

    public function update(Request $request, Plantilla $plantilla)
    {
        $validator = Validator::make($request->all(), [
            'plantilla_item_number'    => 'required|string|max:255',
            'salary_grade'             => 'required|integer|exists:salary_schedules,salary_grade',
            'step'                     => 'required|integer|min:1|max:8',
            'eligibility_requirement'  => 'required|string',
            'educational_requirement'  => 'required|string',
            'department_id'            => 'required|integer|exists:departments,id',
            'division_id'              => 'required|integer|exists:divisions,id',
            'experience'               => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $division = Division::find($request->division_id);

        if (!$division || $division->department_id != $request->department_id) {
            return response()->json([
                'error' => 'The selected division does not belong to the chosen department.'
            ], 422);
        }

        $data = $request->only([
            'plantilla_item_number',
            'salary_grade',
            'step',
            'eligibility_requirement',
            'educational_requirement',
            'department_id',
            'division_id',
            'experience',
        ]);

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                unset($data[$key]);
            }
        }

        $plantilla->update($data);

        return response()->json(
            $plantilla->load('department', 'division')
        );
    }

    public function destroy(Plantilla $plantilla)
    {
        $plantilla->delete();
        return response()->json(['message' => 'Plantilla deleted successfully']);
    }
}
