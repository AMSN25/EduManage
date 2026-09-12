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
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; font-size: 8px; }
        .name-col { text-align: left; min-width: 100px; }
        .roll-col { width: 40px; }
        .total-col { font-weight: bold; background-color: #f9f9f9; }
        .footer { margin-top: 10px; text-align: center; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if($institute->logo)
            <img src="{{ public_path('storage/' . $institute->logo) }}" style="max-height:40px;" alt="Logo">
        @endif
        <div class="institute-name">{{ $institute->name }}</div>
        <div class="title">TABULATION SHEET</div>
        <div class="info">{{ $exam->name }} | {{ $exam->start_date->format('d M Y') }} - {{ $exam->end_date->format('d M Y') }}</div>
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
                <th class="total-col">GPA</th>
                <th class="total-col">Grade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                @php
                    $totalObtained = 0;
                    $totalFull = 0;
                    $subjectResults = [];
                    foreach ($examSubjects as $es) {
                        $mark = $marksData[$student->id][$es->id] ?? null;
                        $obtained = $mark ? ($mark->is_absent ? 0 : $mark->obtained_marks) : 0;
                        $full = $es->full_marks;
                        $totalObtained += $obtained;
                        $totalFull += $full;
                        $percentage = $full > 0 ? round(($obtained / $full) * 100, 2) : 0;
                        $subjectResults[$es->id] = ['obtained' => $obtained, 'full' => $full, 'percentage' => $percentage];
                    }
                    $overallPercentage = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0;
                    // Simple BD grading
                    $grade = match(true) {
                        $overallPercentage >= 80 => 'A+',
                        $overallPercentage >= 70 => 'A',
                        $overallPercentage >= 60 => 'A-',
                        $overallPercentage >= 50 => 'B',
                        $overallPercentage >= 40 => 'C',
                        $overallPercentage >= 33 => 'D',
                        default => 'F',
                    };
                    $gpa = match(true) {
                        $overallPercentage >= 80 => 5.0,
                        $overallPercentage >= 70 => 4.0,
                        $overallPercentage >= 60 => 3.5,
                        $overallPercentage >= 50 => 3.0,
                        $overallPercentage >= 40 => 2.0,
                        $overallPercentage >= 33 => 1.0,
                        default => 0.0,
                    };
                @endphp
                <tr>
                    <td>{{ $student->roll }}</td>
                    <td class="name-col">{{ $student->name }}</td>
                    <td>{{ $student->student_id }}</td>
                    @foreach($examSubjects as $es)
                        <td>{{ $subjectResults[$es->id]['obtained'] }}</td>
                    @endforeach
                    <td class="total-col">{{ $totalObtained }}</td>
                    <td class="total-col">{{ $gpa }}</td>
                    <td class="total-col">{{ $grade }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + count($examSubjects) }}">No students found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | {{ $institute->name }}
    </div>
</body>
</html>
