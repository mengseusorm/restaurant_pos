<?php

namespace App\Exports;

use App\Enums\Status;
use App\Services\ExpensePaymentMethodService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExpensePaymentMethodExport implements FromCollection, WithHeadings
{
    public ExpensePaymentMethodService $expensePaymentMethodService;
    public PaginateRequest $request;

    public function __construct(ExpensePaymentMethodService $expensePaymentMethodService, $request)
    {
        $this->expensePaymentMethodService = $expensePaymentMethodService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $methodArray = [];
        $methods = $this->expensePaymentMethodService->getForExport();

        foreach ($methods as $method) {
            $methodArray[] = [
                $method->id,
                $method->branch ? $method->branch->name : 'N/A',
                $method->name,
                $method->description ?? '',
                $method->is_active == Status::ACTIVE ? trans('all.label.active') : trans('all.label.inactive'),
                $method->created_at ? $method->created_at->format('Y-m-d H:i:s') : '',
                $method->updated_at ? $method->updated_at->format('Y-m-d H:i:s') : '',
            ];
        }
        return collect($methodArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.id'),
            trans('all.label.branch'),
            trans('all.label.name'),
            trans('all.label.description'),
            trans('all.label.status'),
            trans('all.label.created_at'),
            trans('all.label.updated_at'),
        ];
    }
}
