<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\Group;
use App\Models\Section;
use App\Models\Student;
use App\Services\StudentIdGenerator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $instituteId;
    protected $academicYearId;
    protected $errors = [];
    protected $imported = 0;

    public function __construct(int $instituteId, int $academicYearId)
    {
        $this->instituteId = $instituteId;
        $this->academicYearId = $academicYearId;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_bangla' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:5',
            'religion' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'class' => 'required|string|exists:classes,name',
            'section' => 'required|string|exists:sections,name',
            'group' => 'nullable|string|exists:groups,name',
            'roll' => 'nullable|integer|min:1',
            'admission_no' => 'nullable|string|max:30',
            'admission_date' => 'required|date',
        ];
    }

    public function collection(Collection $rows): void
    {
        // All-or-nothing: validate all rows first
        $validatedRows = [];
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because heading is row 1, data starts at row 2

            $class = ClassModel::where('institute_id', $this->instituteId)
                ->where('name', $row['class'])
                ->first();

            if (!$class) {
                $this->errors[] = "Row {$rowNumber}: Class '{$row['class']}' not found";
                continue;
            }

            $section = Section::where('institute_id', $this->instituteId)
                ->where('class_id', $class->id)
                ->where('name', $row['section'])
                ->first();

            if (!$section) {
                $this->errors[] = "Row {$rowNumber}: Section '{$row['section']}' not found in class '{$row['class']}'";
                continue;
            }

            $group = null;
            if (!empty($row['group'])) {
                $group = Group::where('institute_id', $this->instituteId)
                    ->where('name', $row['group'])
                    ->first();

                if (!$group) {
                    $this->errors[] = "Row {$rowNumber}: Group '{$row['group']}' not found";
                    continue;
                }
            }

            $validatedRows[] = [
                'name' => $row['name'],
                'name_bangla' => $row['name_bangla'] ?? null,
                'gender' => $row['gender'],
                'dob' => $row['dob'] ?? null,
                'blood_group' => $row['blood_group'] ?? null,
                'religion' => $row['religion'] ?? null,
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null,
                'class_id' => $class->id,
                'section_id' => $section->id,
                'group_id' => $group?->id,
                'roll' => $row['roll'] ?? null,
                'admission_no' => $row['admission_no'] ?? null,
                'admission_date' => $row['admission_date'],
                'row_number' => $rowNumber,
            ];
        }

        // If any errors, abort entirely (all-or-nothing)
        if (!empty($this->errors)) {
            return;
        }

        // All rows validated — insert them
        $studentIdGenerator = app(StudentIdGenerator::class);
        $institute = \App\Models\Institute::find($this->instituteId);

        foreach ($validatedRows as $rowData) {
            $rowNumber = $rowData['row_number'];
            unset($rowData['row_number']);

            try {
                $student = new Student();
                $student->institute_id = $this->instituteId;
                $student->student_id = $studentIdGenerator->generate($institute);
                $student->academic_year_id = $this->academicYearId;
                $student->fill($rowData);
                $student->save();
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
