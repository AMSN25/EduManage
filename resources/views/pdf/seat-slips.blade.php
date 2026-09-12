<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 10px; }
        .slip { border: 1px solid #000; padding: 10px; margin: 5px 0; display: flex; justify-content: space-between; align-items: center; }
        .slip-info { flex: 1; }
        .slip-label { font-weight: bold; }
        .seat-number { font-size: 28px; font-weight: bold; text-align: center; border: 2px solid #000; padding: 8px 15px; min-width: 60px; }
        .institute { font-size: 10px; color: #666; }
        .exam-name { font-size: 10px; }
        .student-name { font-size: 12px; font-weight: bold; }
        .details { font-size: 9px; color: #555; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($assignments as $index => $assignment)
        <div class="slip">
            <div class="slip-info">
                <div class="institute">{{ $institute->name }}</div>
                <div class="exam-name">{{ $exam->name }}</div>
                <div class="student-name">{{ $assignment->student->name }}</div>
                <div class="details">
                    Roll: {{ $assignment->student->roll }} |
                    ID: {{ $assignment->student->student_id }} |
                    Room: {{ $room->room_name }}
                </div>
            </div>
            <div class="seat-number">{{ $assignment->seat_no }}</div>
        </div>

        @if(!$loop->last && ($index + 1) % 4 === 0)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
