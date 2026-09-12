<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; }
        .institute-name { font-size: 16px; font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; margin-top: 5px; }
        .room-info { font-size: 11px; color: #555; margin-top: 3px; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.grid td { border: 1px solid #000; padding: 6px; text-align: center; vertical-align: middle; width: {{ 100 / $room->columns }}%; height: 40px; }
        .seat-no { font-weight: bold; font-size: 12px; }
        .student-name { font-size: 9px; color: #333; margin-top: 2px; }
        .student-roll { font-size: 8px; color: #666; }
        .empty { background-color: #f5f5f5; color: #999; }
        .legend { margin-top: 15px; font-size: 9px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if($institute->logo)
            <img src="{{ public_path('storage/' . $institute->logo) }}" style="max-height:50px;" alt="Logo">
        @endif
        <div class="institute-name">{{ $institute->name }}</div>
        <div class="title">SEATING CHART</div>
        <div class="room-info">
            Room: {{ $room->room_name }} | Exam: {{ $exam->name }} |
            Capacity: {{ $room->capacity }} | Assigned: {{ $room->assignedCount() }}
        </div>
    </div>

    <table class="grid">
        @for($row = 0; $row < $room->rows; $row++)
            <tr>
                @for($col = 0; $col < $room->columns; $col++)
                    @php
                        $seatNo = ($row * $room->columns) + $col + 1;
                        $assignment = $chart[$row][$col] ?? null;
                    @endphp
                    @if($seatNo <= $room->capacity)
                        @if($assignment)
                            <td>
                                <div class="seat-no">{{ $assignment['seat_no'] }}</div>
                                <div class="student-name">{{ $assignment['student']->name }}</div>
                                <div class="student-roll">Roll: {{ $assignment['student']->roll }}</div>
                            </td>
                        @else
                            <td class="empty">
                                <div class="seat-no">{{ $seatNo }}</div>
                                <div>Empty</div>
                            </td>
                        @endif
                    @else
                        <td class="empty">-</td>
                    @endif
                @endfor
            </tr>
        @endfor
    </table>

    <div class="legend">
        <strong>Legend:</strong> Seat numbers shown. Student name and roll displayed in assigned seats.
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | {{ $institute->name }}
    </div>
</body>
</html>
