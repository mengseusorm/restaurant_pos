<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\PaywayTransactionService;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PaywayTransactionExport implements FromCollection, WithHeadings
{
    public PaywayTransactionService $paywayTransactionService;
    public PaginateRequest $request;

    public function __construct(PaywayTransactionService $paywayTransactionService, $request)
    {
        $this->paywayTransactionService = $paywayTransactionService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $transactionArray = [];
        $transactionsArray = $this->paywayTransactionService->list($this->request); 
        
        foreach ($transactionsArray as $transaction) {
            $transactionArray[] = [
                $transaction->tran_id,  
                number_format((float)$transaction->amount, 2, '.', ''),
                $transaction->currency,
                $transaction->payment_status ?? 'PENDING',
                $transaction->paymentMethod?->name ?? 'N/A',
                AppLibrary::datetime($transaction->created_at),
            ];
        }
        return collect($transactionArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.transaction_id'),
            trans('all.label.amount'), 
            trans('all.label.currency'),
            trans('all.label.payment_status'),
            trans('all.label.payment_method'),  
            trans('all.label.date'),
        ];
    }
}
