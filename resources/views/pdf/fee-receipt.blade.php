<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 15px; }
        .receipt { border: 2px solid #000; padding: 15px; max-width: 400px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .institute-name { font-size: 14px; font-weight: bold; }
        .title { font-size: 12px; font-weight: bold; margin-top: 5px; }
        .receipt-no { font-size: 10px; color: #555; }
        .info-table { width: 100%; margin: 10px 0; }
        .info-table td { padding: 2px 0; vertical-align: top; }
        .label { font-weight: bold; width: 100px; }
        .breakdown { margin: 10px 0; }
        .breakdown table { width: 100%; border-collapse: collapse; }
        .breakdown th, .breakdown td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        .breakdown th { background-color: #f5f5f5; font-size: 10px; }
        .total { font-size: 13px; font-weight: bold; text-align: right; margin: 10px 0; padding: 5px; background: #f0f0f0; }
        .footer { margin-top: 15px; font-size: 9px; color: #666; }
        .signature-line { border-top: 1px solid #000; width: 120px; text-align: center; padding-top: 3px; margin-top: 30px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="receipt">
        @if($institute->logo)
            <div style="text-align:center;">
                <img src="{{ public_path('storage/' . $institute->logo) }}" style="max-height:50px;" alt="Logo">
            </div>
        @endif

        <div class="header">
            <div class="institute-name">{{ $institute->name }}</div>
            @if($institute->address)
                <div style="font-size:9px;">{{ $institute->address }}</div>
            @endif
            <div class="title">FEE RECEIPT</div>
            <div class="receipt-no">Receipt #: {{ $receipt_no }}</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Student:</td>
                <td>{{ $student->name }}</td>
            </tr>
            <tr>
                <td class="label">Student ID:</td>
                <td>{{ $student->student_id }}</td>
            </tr>
            <tr>
                <td class="label">Class:</td>
                <td>{{ $student->classModel->name ?? '' }} {{ $student->section->name ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Roll:</td>
                <td>{{ $student->roll }}</td>
            </tr>
            <tr>
                <td class="label">Date:</td>
                <td>{{ $paid_at->format('d M Y, h:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Collected by:</td>
                <td>{{ $collected_by }}</td>
            </tr>
        </table>

        <div class="breakdown">
            <table>
                <thead>
                    <tr>
                        <th>Fee Type</th>
                        <th>Month</th>
                        <th style="text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->studentFee->feeStructure->feeType->name ?? 'Fee' }}</td>
                            <td>{{ $payment->studentFee->month ?? '-' }}</td>
                            <td style="text-align:right;">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="total">
            Total Paid: {{ number_format($total_paid, 2) }} BDT
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt. No signature required.</p>
            <p>For queries, contact the accounts office.</p>
        </div>

        <div class="signature-line">Collected By</div>
    </div>
</body>
</html>
