<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::with('department')->get();
        return response()->json($divisions);
    }

    public function getByDepartment($departmentId)
    {
        $divisions = Division::where('department_id', $departmentId)->get();
        return response()->json($divisions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'division_name' => 'required|string|max:255',
        ]);

        $division = Division::create([
            'department_id' => $request->department_id,
            'division_name' => $request->division_name,
        ]);

        return response()->json($division, 201);
    }

    public function show(Division $division)
    {
        $division->load('department');
        return response()->json($division);
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'division_name' => 'required|string|max:255',
        ]);

        $division->update([
            'department_id' => $request->department_id,
            'division_name' => $request->division_name,
        ]);

        return response()->json($division);
    }

    public function destroy(Division $division)
    {
        $division->delete();
        return response()->json(['message' => 'Division deleted successfully']);
    }
}
