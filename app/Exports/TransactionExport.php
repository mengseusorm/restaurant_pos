<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\TransactionService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionExport implements FromCollection, WithHeadings
{

    public TransactionService $transactionService;
    public PaginateRequest $request;

    public function __construct(TransactionService $transactionService, $request)
    {
        $this->transactionService = $transactionService;
        $this->request            = $request;
    }

    public function collection() : \Illuminate\Support\Collection
    {
        $transactionArray  = [];
        $transactionsArray = $this->transactionService->list($this->request);

        foreach ($transactionsArray as $transaction) {
            $transactionArray[] = [
                $transaction->order_id,
                '#'.$transaction->order?->order_serial_no,
                $transaction->transaction_no,
                AppLibrary::datetime($transaction->created_at), 
                strtoupper($transaction->payment_method),
                $transaction->type, 
                $transaction->sign,
                AppLibrary::flatAmountFormat($transaction->amount),
                $transaction->currency,
                $transaction->currency_id,
                AppLibrary::flatAmountFormat($transaction->amount_base_currency),
                $transaction->base_currency,
                $transaction->base_currency_id,
                AppLibrary::flatAmountFormat($transaction->transaction_amount),
                $transaction->transaction_currency,
                $transaction->transaction_currency_id,
                AppLibrary::flatAmountFormat($transaction->change_amount),
                $transaction->change_currency,
                $transaction->change_currency_id,
                $transaction->exchange_rate,
                $transaction->note,
            ];
        }
        return collect($transactionArray);
    }

    public function headings() : array
    {
        return [
            trans('all.label.order_id'),
            trans('all.label.order_serial_no'),
            trans('all.label.transaction_id'),
            trans('all.label.date'),
            trans('all.label.payment_method'),
            trans('all.label.type'),
            trans('all.label.sign'),
            trans('all.label.amount'),
            trans('all.label.currency'),
            trans('all.label.currency_id'),
            trans('all.label.amount_base_currency'),
            trans('all.label.base_currency'),
            trans('all.label.base_currency_id'),
            trans('all.label.transaction_amount'),
            trans('all.label.transaction_currency'),
            trans('all.label.transaction_currency_id'),
            trans('all.label.change_amount'),
            trans('all.label.change_currency'),
            trans('all.label.change_currency_id'),
            trans('all.label.exchange_rate'),
            trans('all.label.note'),
        ];
    }
}
