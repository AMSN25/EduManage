<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DuesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        public Collection $students,
    ) {}

    public function collection(): Collection
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Student Name',
            'Class',
            'Section',
            'Roll',
            'Total Due',
            'Total Paid',
            'Remaining',
            'Status',
        ];
    }

    public function map($student): array
    {
        $totalDue = (float) $student->total_due;
        $totalPaid = (float) $student->total_paid;
        $remaining = $totalDue - $totalPaid;

        $status = match (true) {
            $remaining <= 0 => 'Paid',
            $totalPaid > 0 => 'Partial',
            default => 'Unpaid',
        };

        return [
            $student->student_id,
            $student->name,
            $student->classModel?->name ?? '',
            $student->section?->name ?? '',
            $student->roll,
            $totalDue,
            $totalPaid,
            $remaining,
            $status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
