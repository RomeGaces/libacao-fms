<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['department_name' => 'Office of the Municipal Mayor', 'department_code' => 'LM001'],
            ['department_name' => 'Office of the Sangguniang Bayan', 'department_code' => 'LM002'],
            ['department_name' => 'Office of the Municipal Planning and Development Coordinator', 'department_code' => 'LM003'],
            ['department_name' => 'Office of the Municipal Civil Registrar', 'department_code' => 'LM004'],
            ['department_name' => 'Office of the Municipal Budget Officer', 'department_code' => 'LM005'],
            ['department_name' => 'Office of the Municipal Accountant', 'department_code' => 'LM006'],
            ['department_name' => 'Office of the Municipal Treasurer', 'department_code' => 'LM007'],
            ['department_name' => 'Office of the Municipal Assessor', 'department_code' => 'LM008'],
            ['department_name' => 'Office of the Municipal Health Officer', 'department_code' => 'LM009'],
            ['department_name' => 'Office of the Municipal Social Welfare and Development Officer', 'department_code' => 'LM010'],
            ['department_name' => 'Office of the Municipal Agriculturist', 'department_code' => 'LM011'],
            ['department_name' => 'Office of the Municipal Environment and Natural Resources Officer', 'department_code' => 'LM012'],
            ['department_name' => 'Office of the Municipal Engineer', 'department_code' => 'LM013'],
            ['department_name' => 'Office of the Municipal Tourism Officer', 'department_code' => 'LM014'],
        ];

        DB::table('departments')->insert($departments);
    }
}
