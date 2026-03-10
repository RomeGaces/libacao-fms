<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::whereHas('department', function ($q) {
            $q->whereIn('department_code', ['LM001', 'LM005', 'LM006', 'LM007']);
        })->get();

        foreach ($employees as $employee) {
            $user = User::updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'gsis_id' => $employee->gsis_id_no,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'email' => $employee->email ?? strtolower($employee->first_name) . '@example.com',
                    'password' => 'password123',
                    'is_admin' => true,
                    'employee_id' => $employee->id,
                ]
            );

            \Log::info('User seeded', [
                'employee_id' => $employee->id,
                'gsis_id' => $employee->gsis_id_no,
                'user_id' => $user->id,
            ]);
        }

    }
}
