<?php

namespace App\Http\Livewire;

use App\Models\ClassModel;
use App\Models\Group;
use App\Models\Section;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class StudentList extends Component
{
    use WithPagination;

    protected $listeners = ['studentSaved' => '$refresh'];

    public string $search = '';
    public ?int $classFilter = null;
    public ?int $sectionFilter = null;
    public string $statusFilter = 'active';

    protected $queryString = [
        'search' => ['except' => ''],
        'classFilter' => ['except' => null],
        'sectionFilter' => ['except' => null],
        'statusFilter' => ['except' => 'active'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClassFilter(): void
    {
        $this->sectionFilter = null;
        $this->resetPage();
    }

    public function updatedSectionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Student::with(['classModel', 'section', 'group'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('student_id', 'like', "%{$this->search}%")
                  ->orWhere('admission_no', 'like', "%{$this->search}%");
            }))
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->when($this->sectionFilter, fn ($q) => $q->where('section_id', $this->sectionFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('name');

        $students = $query->paginate(15);

        $classes = ClassModel::orderBy('numeric_order')->get();
        $sections = $this->classFilter
            ? Section::where('class_id', $this->classFilter)->get()
            : collect();

        return view('livewire.student-list', [
            'students' => $students,
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }
}
