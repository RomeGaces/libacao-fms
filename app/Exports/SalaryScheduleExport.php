<?php

namespace App\Exports;

use App\Models\SalarySchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalaryScheduleExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return SalarySchedule::select(
            'salary_grade',
            'step_1',
            'step_2',
            'step_3',
            'step_4',
            'step_5',
            'step_6',
            'step_7',
            'step_8'
        )->orderBy('salary_grade')->get();
    }

    public function headings(): array
    {
        return [
            'salary_grade',
            'step_1',
            'step_2',
            'step_3',
            'step_4',
            'step_5',
            'step_6',
            'step_7',
            'step_8',
        ];
    }
}
