<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 9px; margin: 0; padding: 10px; }
        .header { text-align: center; margin-bottom: 10px; }
        .institute-name { font-size: 14px; font-weight: bold; }
        .title { font-size: 12px; font-weight: bold; margin-top: 3px; }
        .info { font-size: 10px; color: #555; }
        .class-header { background-color: #e0e0e0; padding: 5px; margin: 10px 0 5px 0; font-weight: bold; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; font-size: 8px; }
        .name-col { text-align: left; min-width: 100px; }
        .roll-col { width: 40px; }
        .total-col { font-weight: bold; background-color: #f9f9f9; }
        .page-break { page-break-before: always; }
        .footer { margin-top: 10px; text-align: center; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if($institute->logo)
            <img src="{{ public_path('storage/' . $institute->logo) }}" style="max-height:40px;" alt="Logo">
        @endif
        <div class="institute-name">{{ $institute->name }}</div>
        <div class="title">TABULATION SHEET — ALL CLASSES</div>
        <div class="info">{{ $exam->name }} | {{ $exam->start_date->format('d M Y') }} - {{ $exam->end_date->format('d M Y') }}</div>
    </div>

    @foreach($classIds as $classId)
        @php
            $class = \App\Models\ClassModel::find($classId);
            $students = \App\Models\Student::where('institute_id', $institute->id)
                ->where('class_id', $classId)
                ->where('status', 'active')
                ->orderBy('roll')
                ->get();
            $examSubjects = $exam->subjects()->where('class_id', $classId)->with('subject')->get();
        @endphp

        @if(!$loop->first)
            <div class="page-break"></div>
        @endif

        <div class="class-header">
            Class: {{ $class->name ?? 'N/A' }} | Students: {{ $students->count() }}
        </div>

        <table>
            <thead>
                <tr>
                    <th class="roll-col">Roll</th>
                    <th class="name-col">Student Name</th>
                    <th>Student ID</th>
                    @foreach($examSubjects as $es)
                        <th>{{ $es->subject->name ?? 'N/A' }}<br>({{ $es->full_marks }})</th>
                    @endforeach
                    <th class="total-col">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $totalObtained = 0;
                        $totalFull = 0;
                        foreach ($examSubjects as $es) {
                            $mark = $es->marks()->where('student_id', $student->id)->first();
                            $obtained = $mark ? ($mark->is_absent ? 0 : $mark->obtained_marks) : 0;
                            $totalObtained += $obtained;
                            $totalFull += $es->full_marks;
                        }
                    @endphp
                    <tr>
                        <td>{{ $student->roll }}</td>
                        <td class="name-col">{{ $student->name }}</td>
                        <td>{{ $student->student_id }}</td>
                        @foreach($examSubjects as $es)
                            @php
                                $mark = $es->marks()->where('student_id', $student->id)->first();
                            @endphp
                            <td>{{ $mark ? ($mark->is_absent ? 'Absent' : $mark->obtained_marks) : '-' }}</td>
                        @endforeach
                        <td class="total-col">{{ $totalObtained }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + count($examSubjects) }}">No students</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | {{ $institute->name }}
    </div>
</body>
</html>
