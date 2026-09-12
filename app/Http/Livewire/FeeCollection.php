<?php

namespace App\Http\Livewire;

use App\Models\FeePayment;
use App\Models\Institute;
use App\Models\Student;
use App\Models\StudentFee;
use App\Services\FeeReceiptGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FeeCollection extends Component
{
    public $search = '';
    public $studentId = '';
    public $selectedFees = [];
    public $paymentAmounts = [];
    public $paymentMethod = 'cash';
    public $student = null;
    public $outstandingFees = [];
    public $showReceipt = false;
    public $receiptData = null;

    public function searchStudent(): void
    {
        if (strlen($this->search) < 2) {
            $this->student = null;
            $this->outstandingFees = [];
            return;
        }

        $instituteId = Auth::user()->institute_id;

        $this->student = Student::where('institute_id', $instituteId)
            ->where(function ($q) {
                $q->where('student_id', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->with(['classModel', 'section'])
            ->first();

        if ($this->student) {
            $this->studentId = $this->student->id;
            $this->loadOutstandingFees();
        } else {
            $this->outstandingFees = [];
        }
    }

    public function selectStudent(int $studentId): void
    {
        $instituteId = Auth::user()->institute_id;

        $this->student = Student::where('institute_id', $instituteId)
            ->where('id', $studentId)
            ->with(['classModel', 'section'])
            ->first();

        if ($this->student) {
            $this->studentId = $this->student->id;
            $this->search = $this->student->student_id;
            $this->loadOutstandingFees();
        }
    }

    public function loadOutstandingFees(): void
    {
        if (!$this->student) {
            return;
        }

        $this->outstandingFees = StudentFee::where('student_id', $this->student->id)
            ->unpaidOrPartial()
            ->with('feeStructure.feeType')
            ->orderByDesc('due_date')
            ->get()
            ->toArray();

        $this->selectedFees = [];
        $this->paymentAmounts = [];
    }

    public function toggleFee(int $studentFeeId): void
    {
        $index = array_search($studentFeeId, $this->selectedFees);

        if ($index === false) {
            $this->selectedFees[] = $studentFeeId;
            // Default payment amount = remaining due
            $fee = collect($this->outstandingFees)->firstWhere('id', $studentFeeId);
            if ($fee) {
                $remaining = (float) $fee['amount_due'] - (float) $fee['amount_paid'];
                $this->paymentAmounts[$studentFeeId] = $remaining;
            }
        } else {
            unset($this->selectedFees[$index]);
            unset($this->paymentAmounts[$studentFeeId]);
            $this->selectedFees = array_values($this->selectedFees);
        }
    }

    public function selectAll(): void
    {
        $this->selectedFees = [];
        $this->paymentAmounts = [];

        foreach ($this->outstandingFees as $fee) {
            $this->selectedFees[] = $fee['id'];
            $remaining = (float) $fee['amount_due'] - (float) $fee['amount_paid'];
            $this->paymentAmounts[$fee['id']] = $remaining;
        }
    }

    public function processPayment(): void
    {
        if (empty($this->selectedFees)) {
            session()->flash('error', __('fees.no_fees_selected'));
            return;
        }

        if (!$this->student) {
            session()->flash('error', __('fees.student_not_found'));
            return;
        }

        // Validate amounts
        foreach ($this->selectedFees as $studentFeeId) {
            $amount = (float) ($this->paymentAmounts[$studentFeeId] ?? 0);
            if ($amount <= 0) {
                session()->flash('error', __('fees.invalid_amount'));
                return;
            }

            $fee = collect($this->outstandingFees)->firstWhere('id', $studentFeeId);
            if ($fee) {
                $remaining = (float) $fee['amount_due'] - (float) $fee['amount_paid'];
                if ($amount > $remaining) {
                    session()->flash('error', __('fees.amount_exceeds_due', [
                        'name' => $fee['fee_structure']['fee_type']['name'] ?? 'Fee',
                    ]));
                    return;
                }
            }
        }

        $receiptGenerator = new FeeReceiptGenerator();
        $institute = Institute::findOrFail(Auth::user()->institute_id);

        try {
            $receiptNo = DB::transaction(function () use ($receiptGenerator, $institute) {
                return $receiptGenerator->generate($institute);
            });

            $payments = DB::transaction(function () use ($receiptNo) {
                $payments = [];

                foreach ($this->selectedFees as $studentFeeId) {
                    $amount = (float) $this->paymentAmounts[$studentFeeId];

                    $studentFee = StudentFee::lockForUpdate()->find($studentFeeId);

                    if (!$studentFee) {
                        throw new \Exception(__('fees.fee_not_found'));
                    }

                    $remaining = (float) $studentFee->amount_due - (float) $studentFee->amount_paid;

                    if ($amount > $remaining) {
                        throw new \Exception(__('fees.amount_exceeds_due', [
                            'name' => $studentFee->feeStructure->feeType->name ?? 'Fee',
                        ]));
                    }

                    $newPaid = (float) $studentFee->amount_paid + $amount;

                    $studentFee->update([
                        'amount_paid' => $newPaid,
                        'status' => $newPaid >= (float) $studentFee->amount_due ? 'paid' : 'partial',
                    ]);

                    $payment = FeePayment::create([
                        'institute_id' => Auth::user()->institute_id,
                        'student_fee_id' => $studentFeeId,
                        'amount' => $amount,
                        'payment_method' => $this->paymentMethod,
                        'receipt_no' => $receiptNo,
                        'collected_by' => Auth::id(),
                        'paid_at' => now(),
                    ]);

                    $payments[] = $payment->load('studentFee.feeStructure.feeType');
                }

                return $payments;
            });

            // Build receipt data
            $totalPaid = collect($payments)->sum('amount');
            $this->receiptData = [
                'receipt_no' => $receiptNo,
                'student' => $this->student,
                'payments' => $payments,
                'total_paid' => $totalPaid,
                'collected_by' => Auth::user()->name,
                'paid_at' => now(),
                'institute' => $institute,
            ];

            $this->showReceipt = true;
            $this->loadOutstandingFees();

            session()->flash('success', __('fees.payment_processed'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function downloadReceipt(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$this->receiptData) {
            abort(404);
        }

        $pdfService = new \App\Services\PdfGenerationService();
        $view = view('pdf.fee-receipt', $this->receiptData);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view->render())
            ->setPaper('a5', 'portrait')
            ->setWarnings(false);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'receipt_' . $this->receiptData['receipt_no'] . '.pdf');
    }

    public function render()
    {
        return view('livewire.fee-collection');
    }
}
