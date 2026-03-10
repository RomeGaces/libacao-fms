<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'mayor@example.com',
                'gsis_id_no' => 'GSIS001',
                'department_code' => 'LM001',
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'budget@example.com',
                'gsis_id_no' => 'GSIS002',
                'department_code' => 'LM005',
            ],
            [
                'first_name' => 'Jose',
                'last_name' => 'Reyes',
                'email' => 'accountant@example.com',
                'gsis_id_no' => 'GSIS003',
                'department_code' => 'LM006',
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Lopez',
                'email' => 'treasurer@example.com',
                'gsis_id_no' => 'GSIS004',
                'department_code' => 'LM007',
            ],
        ];

        foreach ($employees as $emp) {
            $department = Department::where('department_code', $emp['department_code'])->first();

            DB::table('employees')->insert([
                'first_name' => $emp['first_name'],
                'last_name' => $emp['last_name'],
                'email' => $emp['email'],
                'gsis_id_no' => $emp['gsis_id_no'],
                'department_id' => $department?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
