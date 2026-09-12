<?php

namespace App\Http\Livewire;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Group;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Services\StudentIdGenerator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentForm extends Component
{
    use WithFileUploads;

    protected $listeners = [
        'openStudentForm' => 'openModal',
        'editStudent' => 'editStudent',
    ];

    public ?int $studentId = null;
    public string $activeTab = 'basic';

    // Basic Info
    public string $name = '';
    public string $name_bangla = '';
    public string $gender = 'male';
    public ?string $dob = null;
    public string $blood_group = '';
    public string $religion = '';
    public string $phone = '';
    public string $email = '';
    public $photo;

    // Academic
    public ?int $class_id = null;
    public ?int $section_id = null;
    public ?int $group_id = null;
    public ?int $academic_year_id = null;
    public ?string $admission_date = null;
    public string $admission_no = '';
    public ?int $roll = null;
    public string $status = 'active';

    // Address
    public string $present_village = '';
    public string $present_post_office = '';
    public string $present_upazila = '';
    public string $present_district = '';
    public string $present_division = '';
    public bool $same_as_present = false;
    public string $permanent_village = '';
    public string $permanent_post_office = '';
    public string $permanent_upazila = '';
    public string $permanent_district = '';
    public string $permanent_division = '';

    // Guardian
    public string $father_name = '';
    public string $father_phone = '';
    public string $father_occupation = '';
    public string $father_nid = '';
    public string $mother_name = '';
    public string $mother_phone = '';
    public string $mother_occupation = '';
    public string $mother_nid = '';
    public string $guardian_name = '';
    public string $guardian_phone = '';
    public string $guardian_occupation = '';
    public string $guardian_nid = '';
    public string $guardian_relation = '';

    public bool $showModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'name_bangla' => 'nullable|string|max:255',
        'gender' => 'required|in:male,female,other',
        'dob' => 'nullable|date',
        'blood_group' => 'nullable|string|max:5',
        'religion' => 'nullable|string|max:50',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email',
        'photo' => 'nullable|image|max:2048',
        'class_id' => 'required|exists:classes,id',
        'section_id' => 'required|exists:sections,id',
        'group_id' => 'nullable|exists:groups,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'admission_date' => 'required|date',
        'admission_no' => 'nullable|string|max:30',
        'roll' => 'nullable|integer|min:1',
        'status' => 'required|in:active,inactive,transferred,graduated',
        'present_village' => 'nullable|string|max:255',
        'present_post_office' => 'nullable|string|max:255',
        'present_upazila' => 'nullable|string|max:255',
        'present_district' => 'nullable|string|max:255',
        'present_division' => 'nullable|string|max:255',
        'permanent_village' => 'nullable|string|max:255',
        'permanent_post_office' => 'nullable|string|max:255',
        'permanent_upazila' => 'nullable|string|max:255',
        'permanent_district' => 'nullable|string|max:255',
        'permanent_division' => 'nullable|string|max:255',
        'father_name' => 'nullable|string|max:255',
        'father_phone' => 'nullable|string|max:20',
        'father_occupation' => 'nullable|string|max:255',
        'father_nid' => 'nullable|string|max:20',
        'mother_name' => 'nullable|string|max:255',
        'mother_phone' => 'nullable|string|max:20',
        'mother_occupation' => 'nullable|string|max:255',
        'mother_nid' => 'nullable|string|max:20',
        'guardian_name' => 'nullable|string|max:255',
        'guardian_phone' => 'nullable|string|max:20',
        'guardian_occupation' => 'nullable|string|max:255',
        'guardian_nid' => 'nullable|string|max:20',
        'guardian_relation' => 'nullable|string|max:50',
    ];

    public function mount(?int $studentId = null): void
    {
        $this->academic_year_id = AcademicYear::current()->first()?->id;
        $this->admission_date = now()->format('Y-m-d');

        if ($studentId) {
            $this->loadStudent($studentId);
        }
    }

    public function updatedSameAsPresent($value): void
    {
        if ($value) {
            $this->permanent_village = $this->present_village;
            $this->permanent_post_office = $this->present_post_office;
            $this->permanent_upazila = $this->present_upazila;
            $this->permanent_district = $this->present_district;
            $this->permanent_division = $this->present_division;
        }
    }

    public function updatedClassId(): void
    {
        $this->section_id = null;
        $this->group_id = null;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editStudent(array $params): void
    {
        $studentId = $params['studentId'] ?? null;
        if ($studentId) {
            $this->loadStudent($studentId);
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $institute = auth()->user()->institute;

            // Plan enforcement: check if institute can add more students
            if (!$this->studentId) {
                $subscription = $institute->currentSubscription();
                if ($subscription && $subscription->plan) {
                    $currentCount = $institute->students()->count();
                    if ($currentCount >= $subscription->plan->max_students) {
                        $this->addError('plan', 'Your subscription plan has reached the maximum student limit. Please upgrade your plan.');
                        return;
                    }
                }
            }

            if ($this->studentId) {
                $student = Student::findOrFail($this->studentId);
            } else {
                $studentIdGenerator = app(StudentIdGenerator::class);
                $student = new Student();
                $student->student_id = $studentIdGenerator->generate($institute);
            }

            if ($this->photo) {
                $path = $this->photo->store(
                    'institutes/' . $institute->id . '/students',
                    'public'
                );
                $student->photo_path = $path;
            }

            $student->fill([
                'institute_id' => $institute->id,
                'name' => $this->name,
                'name_bangla' => $this->name_bangla,
                'gender' => $this->gender,
                'dob' => $this->dob,
                'blood_group' => $this->blood_group,
                'religion' => $this->religion,
                'phone' => $this->phone,
                'email' => $this->email,
                'class_id' => $this->class_id,
                'section_id' => $this->section_id,
                'group_id' => $this->group_id,
                'academic_year_id' => $this->academic_year_id,
                'admission_date' => $this->admission_date,
                'admission_no' => $this->admission_no,
                'roll' => $this->roll,
                'status' => $this->status,
                'present_village' => $this->present_village,
                'present_post_office' => $this->present_post_office,
                'present_upazila' => $this->present_upazila,
                'present_district' => $this->present_district,
                'present_division' => $this->present_division,
                'permanent_village' => $this->permanent_village,
                'permanent_post_office' => $this->permanent_post_office,
                'permanent_upazila' => $this->permanent_upazila,
                'permanent_district' => $this->permanent_district,
                'permanent_division' => $this->permanent_division,
            ]);

            $student->save();

            // Save guardians
            $this->saveGuardians($student);
        });

        session()->flash('success', __('student.student_saved'));
        $this->closeModal();
        $this->dispatch('studentSaved');
    }

    private function saveGuardians(Student $student): void
    {
        $student->guardians()->delete();

        if ($this->father_name) {
            $student->guardians()->create([
                'relation' => 'father',
                'name' => $this->father_name,
                'phone' => $this->father_phone,
                'occupation' => $this->father_occupation,
                'nid' => $this->father_nid,
            ]);
        }

        if ($this->mother_name) {
            $student->guardians()->create([
                'relation' => 'mother',
                'name' => $this->mother_name,
                'phone' => $this->mother_phone,
                'occupation' => $this->mother_occupation,
                'nid' => $this->mother_nid,
            ]);
        }

        if ($this->guardian_name) {
            $student->guardians()->create([
                'relation' => 'guardian',
                'name' => $this->guardian_name,
                'phone' => $this->guardian_phone,
                'occupation' => $this->guardian_occupation,
                'nid' => $this->guardian_nid,
            ]);
        }
    }

    private function loadStudent(int $studentId): void
    {
        $student = Student::with('guardians')->findOrFail($studentId);
        $this->studentId = $student->id;
        $this->name = $student->name;
        $this->name_bangla = $student->name_bangla ?? '';
        $this->gender = $student->gender;
        $this->dob = $student->dob?->format('Y-m-d');
        $this->blood_group = $student->blood_group ?? '';
        $this->religion = $student->religion ?? '';
        $this->phone = $student->phone ?? '';
        $this->email = $student->email ?? '';
        $this->class_id = $student->class_id;
        $this->section_id = $student->section_id;
        $this->group_id = $student->group_id;
        $this->academic_year_id = $student->academic_year_id;
        $this->admission_date = $student->admission_date->format('Y-m-d');
        $this->admission_no = $student->admission_no ?? '';
        $this->roll = $student->roll;
        $this->status = $student->status;

        $this->present_village = $student->present_village ?? '';
        $this->present_post_office = $student->present_post_office ?? '';
        $this->present_upazila = $student->present_upazila ?? '';
        $this->present_district = $student->present_district ?? '';
        $this->present_division = $student->present_division ?? '';
        $this->permanent_village = $student->permanent_village ?? '';
        $this->permanent_post_office = $student->permanent_post_office ?? '';
        $this->permanent_upazila = $student->permanent_upazila ?? '';
        $this->permanent_district = $student->permanent_district ?? '';
        $this->permanent_division = $student->permanent_division ?? '';

        $guardians = $student->guardians->keyBy('relation');
        $this->father_name = $guardians['father']->name ?? '';
        $this->father_phone = $guardians['father']->phone ?? '';
        $this->father_occupation = $guardians['father']->occupation ?? '';
        $this->father_nid = $guardians['father']->nid ?? '';
        $this->mother_name = $guardians['mother']->name ?? '';
        $this->mother_phone = $guardians['mother']->phone ?? '';
        $this->mother_occupation = $guardians['mother']->occupation ?? '';
        $this->mother_nid = $guardians['mother']->nid ?? '';
        $this->guardian_name = $guardians['guardian']->name ?? '';
        $this->guardian_phone = $guardians['guardian']->phone ?? '';
        $this->guardian_occupation = $guardians['guardian']->occupation ?? '';
        $this->guardian_nid = $guardians['guardian']->nid ?? '';
        $this->guardian_relation = $guardians['guardian']->relation ?? '';
    }

    private function resetForm(): void
    {
        $this->reset([
            'studentId', 'name', 'name_bangla', 'gender', 'dob', 'blood_group',
            'religion', 'phone', 'email', 'photo', 'class_id', 'section_id',
            'group_id', 'admission_no', 'roll', 'status',
            'present_village', 'present_post_office', 'present_upazila',
            'present_district', 'present_division', 'same_as_present',
            'permanent_village', 'permanent_post_office', 'permanent_upazila',
            'permanent_district', 'permanent_division',
            'father_name', 'father_phone', 'father_occupation', 'father_nid',
            'mother_name', 'mother_phone', 'mother_occupation', 'mother_nid',
            'guardian_name', 'guardian_phone', 'guardian_occupation', 'guardian_nid',
            'guardian_relation', 'activeTab',
        ]);
        $this->academic_year_id = AcademicYear::current()->first()?->id;
        $this->admission_date = now()->format('Y-m-d');
        $this->status = 'active';
        $this->gender = 'male';
    }

    public function render()
    {
        $classes = ClassModel::orderBy('numeric_order')->get();
        $sections = $this->class_id
            ? Section::where('class_id', $this->class_id)->get()
            : collect();
        $groups = $this->class_id
            ? Group::whereHas('classes', fn ($q) => $q->where('class_id', $this->class_id))->get()
            : collect();
        $academicYears = AcademicYear::orderByDesc('is_current')->orderByDesc('name')->get();

        return view('livewire.student-form', [
            'classes' => $classes,
            'sections' => $sections,
            'groups' => $groups,
            'academicYears' => $academicYears,
        ]);
    }
}
