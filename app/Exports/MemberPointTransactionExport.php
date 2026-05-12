<?php

namespace App\Exports;

use App\Services\MemberPointTransactionService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class MemberPointTransactionExport implements FromCollection, WithHeadings
{
    public MemberPointTransactionService $transactionService;
    public PaginateRequest $request;

    public function __construct(MemberPointTransactionService $transactionService, $request)
    {
        $this->transactionService = $transactionService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $transactionArray = [];
        $transactions = $this->transactionService->list($this->request);

        foreach ($transactions as $transaction) {
            $transactionArray[] = [
                $transaction->id,
                $transaction->member ? $transaction->member->name : 'N/A',
                $transaction->member ? $transaction->member->phone : 'N/A',
                $transaction->member ? $transaction->member->card_number : 'N/A',
                $this->getFormattedType($transaction->type),
                $transaction->points,
                $transaction->reference_type ?? 'N/A',
                $transaction->reference_id ?? 'N/A',
                $transaction->note ?? 'N/A',
                $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : '',
            ];
        }
        return collect($transactionArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.id'),
            trans('all.label.member_name'),
            trans('all.label.member_phone'),
            trans('all.label.card_number'),
            trans('all.label.transaction_type'),
            trans('all.label.points'),
            trans('all.label.reference_type'),
            trans('all.label.reference_id'),
            trans('all.label.note'),
            trans('all.label.created_at'),
        ];
    }

    /**
     * Get formatted transaction type for display
     */
    private function getFormattedType(string $type): string
    {
        $typeMap = [
            'earn' => trans('all.label.points_earned'),
            'redeem' => trans('all.label.points_redeemed'),
            'revert_earn' => trans('all.label.earn_reverted'),
            'revert_redeem' => trans('all.label.redeem_reverted'),
        ];

        return $typeMap[$type] ?? ucfirst($type);
    }
}
