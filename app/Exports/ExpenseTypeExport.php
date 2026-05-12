<?php

namespace App\Exports;

use App\Enums\Status;
use App\Services\ExpenseTypeService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExpenseTypeExport implements FromCollection, WithHeadings
{
    public ExpenseTypeService $expenseTypeService;
    public PaginateRequest $request;

    public function __construct(ExpenseTypeService $expenseTypeService, $request)
    {
        $this->expenseTypeService = $expenseTypeService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $typeArray = [];
        $types = $this->expenseTypeService->getForExport();

        foreach ($types as $type) {
            $typeArray[] = [
                $type->id,
                $type->branch ? $type->branch->name : 'N/A',
                $type->name,
                $type->description ?? '',
                $type->is_active == Status::ACTIVE ? trans('all.label.active') : trans('all.label.inactive'),
                $type->created_at ? $type->created_at->format('Y-m-d H:i:s') : '',
                $type->updated_at ? $type->updated_at->format('Y-m-d H:i:s') : '',
            ];
        }
        return collect($typeArray);
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
