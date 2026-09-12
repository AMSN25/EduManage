<?php

namespace App\Http\Livewire;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Group;
use App\Models\Section;
use App\Models\Subject;
use Livewire\Component;

class ClassSetupWizard extends Component
{
    public ?int $academicYearId = null;
    public int $step = 1;

    public array $classes = [];
    public array $sections = [];
    public array $classSubjects = [];

    public function mount(): void
    {
        $currentYear = AcademicYear::current()->first();
        if ($currentYear) {
            $this->academicYearId = $currentYear->id;
        }
        $this->loadClasses();
    }

    public function updatedAcademicYearId(): void
    {
        $this->loadClasses();
    }

    private function loadClasses(): void
    {
        if (!$this->academicYearId) {
            $this->classes = [];
            return;
        }

        $dbClasses = ClassModel::orderBy('numeric_order')->get();
        $this->classes = $dbClasses->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'numeric_order' => $c->numeric_order,
            'sections' => $c->sections->pluck('name', 'id'),
            'subjects' => $c->subjects->pluck('name', 'id'),
        ])->toArray();
    }

    public function addClass(): void
    {
        $this->classes[] = [
            'id' => null,
            'name' => '',
            'numeric_order' => count($this->classes) + 1,
            'sections' => [],
            'subjects' => [],
        ];
    }

    public function removeClass(int $index): void
    {
        unset($this->classes[$index]);
        $this->classes = array_values($this->classes);
    }

    public function addSection(int $classIndex): void
    {
        $this->classes[$classIndex]['sections'][] = '';
    }

    public function removeSection(int $classIndex, int $sectionIndex): void
    {
        unset($this->classes[$classIndex]['sections'][$sectionIndex]);
        $this->classes[$classIndex]['sections'] = array_values($this->classes[$classIndex]['sections']);
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate(['academicYearId' => 'required|exists:academic_years,id']);
            $this->step = 2;
        } elseif ($this->step === 2) {
            $this->saveClasses();
            $this->step = 3;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function saveAll(): void
    {
        $this->saveClasses();
        $this->saveSubjects();
        $this->loadClasses();
        session()->flash('success', __('academic_structure.wizard_saved'));
    }

    private function saveClasses(): void
    {
        foreach ($this->classes as &$classData) {
            if (empty($classData['name'])) continue;

            $class = ClassModel::updateOrCreate(
                ['id' => $classData['id']],
                [
                    'name' => $classData['name'],
                    'numeric_order' => $classData['numeric_order'],
                ]
            );

            $classData['id'] = $class->id;

            foreach ($classData['sections'] as $sectionName) {
                if (empty($sectionName)) continue;
                Section::updateOrCreate(
                    ['class_id' => $class->id, 'name' => $sectionName],
                    ['name' => $sectionName]
                );
            }
        }
    }

    private function saveSubjects(): void
    {
        foreach ($this->classes as $classData) {
            if (empty($classData['id'])) continue;

            $class = ClassModel::find($classData['id']);
            if (!$class) continue;

            $subjectIds = [];
            foreach ($classData['subjects'] ?? [] as $subjectName) {
                if (empty($subjectName)) continue;
                $subject = Subject::firstOrCreate(
                    ['name' => $subjectName],
                    ['name' => $subjectName]
                );
                $subjectIds[] = $subject->id;
            }

            $class->subjects()->sync($subjectIds);
        }
    }

    public function render()
    {
        return view('livewire.class-setup-wizard', [
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'allSubjects' => Subject::orderBy('name')->get(),
            'allGroups' => Group::orderBy('name')->get(),
        ]);
    }
}
