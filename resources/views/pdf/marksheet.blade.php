<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .institute-name { font-size: 18px; font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; margin-top: 5px; }
        .info { font-size: 11px; color: #555; }
        .student-info { margin: 15px 0; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 3px 8px; vertical-align: top; font-size: 11px; }
        .label { font-weight: bold; width: 120px; }
        .marks-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .marks-table th, .marks-table td { border: 1px solid #000; padding: 5px 8px; text-align: center; }
        .marks-table th { background-color: #e0e0e0; font-weight: bold; font-size: 10px; }
        .marks-table td { font-size: 11px; }
        .summary-row { background-color: #f0f0f0; font-weight: bold; }
        .total-col { font-weight: bold; background-color: #f9f9f9; }
        .footer { margin-top: 25px; display: flex; justify-content: space-between; }
        .signature-line { border-top: 1px solid #000; width: 150px; text-align: center; padding-top: 3px; font-size: 10px; }
        .signature-box { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        @if($institute->logo)
            <img src="{{ public_path('storage/' . $institute->logo) }}" style="max-height:50px;" alt="Logo">
        @endif
        <div class="institute-name">{{ $institute->name }}</div>
        @if($institute->address)
            <div style="font-size:10px;">{{ $institute->address }}</div>
        @endif
        <div class="title">MARKSHEET</div>
        <div class="info">{{ $exam->name }} | {{ $exam->start_date->format('d M Y') }} - {{ $exam->end_date->format('d M Y') }}</div>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Name:</td>
                <td>{{ $student->name }}</td>
                <td class="label">Roll No:</td>
                <td>{{ $student->roll }}</td>
            </tr>
            <tr>
                <td class="label">Student ID:</td>
                <td>{{ $student->student_id }}</td>
                <td class="label">Class:</td>
                <td>{{ $student->classModel?->name ?? '' }} {{ $student->section?->name ?? '' }}</td>
            </tr>
            @if($student->group)
                <tr>
                    <td class="label">Group:</td>
                    <td>{{ $student->group->name }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif
        </table>
    </div>

    <table class="marks-table">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th style="text-align:left;">Subject</th>
                <th>Full Marks</th>
                <th>Obtained</th>
                <th>Grade</th>
                <th>GPA Point</th>
            </tr>
        </thead>
        <tbody>
            @foreach($breakdowns as $index => $bd)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align:left;">{{ $bd->subject->name ?? 'N/A' }}</td>
                    <td>{{ number_format((float)$bd->full, 2) }}</td>
                    <td>{{ number_format((float)$bd->obtained, 2) }}</td>
                    <td>{{ $bd->grade }}</td>
                    <td>{{ number_format((float)$bd->gpa_point, 1) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td colspan="2" style="text-align:left;">Total</td>
                <td>{{ number_format((float)$result->total_full, 2) }}</td>
                <td>{{ number_format((float)$result->total_obtained, 2) }}</td>
                <td>{{ $result->grade }}</td>
                <td>{{ number_format((float)$result->gpa, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table style="width:100%; border-collapse:collapse; margin-top:10px;">
        <tr>
            <td style="border:1px solid #000; padding:5px 8px; font-weight:bold; width:25%;">Percentage</td>
            <td style="border:1px solid #000; padding:5px 8px; width:25%;">{{ number_format((float)$result->percentage, 2) }}%</td>
            <td style="border:1px solid #000; padding:5px 8px; font-weight:bold; width:25%;">Position</td>
            <td style="border:1px solid #000; padding:5px 8px; width:25%;">{{ $result->position ? $this->ordinal($result->position) : 'N/A' }}</td>
        </tr>
    </table>

    <div class="footer">
        <div class="signature-box">
            <div class="signature-line">Student's Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Class Teacher's Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Principal's Signature</div>
        </div>
    </div>
</body>
</html>
