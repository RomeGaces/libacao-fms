<?php

namespace App\Imports;

use App\Models\SalarySchedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalaryScheduleImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $row = array_map(fn($value) => is_string($value) ? trim($value) : $value, $row);

        if (!isset($row['salary_grade']) || !is_numeric($row['salary_grade'])) {
            return null;
        }

        $steps = [];
        for ($i = 1; $i <= 8; $i++) {
            $steps["step_$i"] = isset($row["step_$i"]) && $row["step_$i"] !== '' ? $row["step_$i"] : null;
        }

        return SalarySchedule::updateOrCreate(
            ['salary_grade' => $row['salary_grade']],
            $steps
        );
    }
}