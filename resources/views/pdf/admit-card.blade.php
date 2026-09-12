<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        .card { border: 2px solid #000; padding: 15px; width: 100%; box-sizing: border-box; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .institute-name { font-size: 16px; font-weight: bold; }
        .logo { max-height: 60px; margin-bottom: 5px; }
        .title { font-size: 14px; font-weight: bold; margin-top: 5px; }
        .exam-info { font-size: 11px; color: #333; }
        .student-info { margin: 10px 0; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 3px 5px; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }
        .photo { float: right; width: 80px; height: 100px; border: 1px solid #ccc; object-fit: cover; }
        .footer { margin-top: 15px; display: flex; justify-content: space-between; }
        .signature-line { border-top: 1px solid #000; width: 150px; text-align: center; padding-top: 3px; font-size: 10px; }
        .signature-box { text-align: center; }
        .instructions { font-size: 9px; margin-top: 10px; border-top: 1px dashed #999; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="card">
        @if($institute->logo)
            <div style="text-align:center;">
                <img src="{{ public_path('storage/' . $institute->logo) }}" class="logo" alt="Logo">
            </div>
        @endif

        <div class="header">
            <div class="institute-name">{{ $institute->name }}</div>
            @if($institute->address)
                <div style="font-size:10px;">{{ $institute->address }}</div>
            @endif
            <div class="title">ADMIT CARD</div>
            <div class="exam-info">{{ $exam->name }} | {{ $exam->start_date->format('d M Y') }} - {{ $exam->end_date->format('d M Y') }}</div>
        </div>

        @if($student->photo_path)
            <img src="{{ public_path('storage/' . $student->photo_path) }}" class="photo" alt="Photo">
        @endif

        <div class="student-info">
            <table>
                <tr>
                    <td class="label">Name:</td>
                    <td>{{ $student->name }}</td>
                </tr>
                <tr>
                    <td class="label">Roll No:</td>
                    <td>{{ $student->roll }}</td>
                </tr>
                <tr>
                    <td class="label">Student ID:</td>
                    <td>{{ $student->student_id }}</td>
                </tr>
                <tr>
                    <td class="label">Class:</td>
                    <td>{{ $student->classModel->name ?? '' }} {{ $student->section->name ?? '' }}</td>
                </tr>
                @if($student->group)
                    <tr>
                        <td class="label">Group:</td>
                        <td>{{ $student->group->name }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Father's Name:</td>
                    <td>{{ $student->guardians->where('relation', 'father')->first()->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="instructions">
            <strong>Instructions:</strong><br>
            1. Bring this admit card to all exam halls.<br>
            2. Arrive at least 30 minutes before the exam starts.<br>
            3. No electronic devices allowed in the exam hall.<br>
            4. Follow all exam regulations strictly.
        </div>

        <div class="footer">
            <div class="signature-box">
                <div class="signature-line">Student's Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Principal's Signature</div>
            </div>
        </div>
    </div>
</body>
</html>
