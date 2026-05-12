<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping
{
    public Collection $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            'Expense Code',
            'Branch',
            'Expense Date',
            'Expense Type',
            'Amount',
            'Payment Method',
            'Paid To',
            'Reference No',
            'Description',
            'Recorded By',
            'Approved By',
            'Status'
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_code,
            $expense->branch ? $expense->branch->name : '',
            $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '',
            $expense->expenseType ? $expense->expenseType->name : '',
            number_format($expense->amount, 2),
            $expense->paymentMethod ? $expense->paymentMethod->name : '',
            $expense->paid_to,
            $expense->reference_no,
            $expense->description,
            $expense->recordedBy ? $expense->recordedBy->name : '',
            $expense->approvedBy ? $expense->approvedBy->name : '',
            ucfirst($expense->status)
        ];
    }
}
