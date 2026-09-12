<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Name',
            'Name (Bangla)',
            'Gender',
            'DOB',
            'Blood Group',
            'Religion',
            'Phone',
            'Email',
            'Class',
            'Section',
            'Group',
            'Roll',
            'Admission No',
            'Admission Date',
            'Status',
        ];
    }

    public function map($student): array
    {
        return [
            $student->student_id,
            $student->name,
            $student->name_bangla,
            $student->gender,
            $student->dob?->format('Y-m-d'),
            $student->blood_group,
            $student->religion,
            $student->phone,
            $student->email,
            $student->classModel?->name,
            $student->section?->name,
            $student->group?->name,
            $student->roll,
            $student->admission_no,
            $student->admission_date?->format('Y-m-d'),
            $student->status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
