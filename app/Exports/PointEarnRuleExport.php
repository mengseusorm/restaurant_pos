<?php

namespace App\Exports;

use App\Services\PointEarnRuleService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PointEarnRuleExport implements FromCollection, WithHeadings
{
    public PointEarnRuleService $pointEarnRuleService;
    public PaginateRequest $request;

    public function __construct(PointEarnRuleService $pointEarnRuleService, $request)
    {
        $this->pointEarnRuleService = $pointEarnRuleService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $ruleArray = [];
        $rules = $this->pointEarnRuleService->getForExport();

        foreach ($rules as $rule) {
            $ruleArray[] = [
                $rule->id,
                $rule->branch ? $rule->branch->name : 'N/A',
                $rule->currency_amount,
                $rule->point,
                round($rule->point / $rule->currency_amount, 2),
                $rule->is_active ? trans('all.label.active') : trans('all.label.inactive'),
                $rule->created_at ? $rule->created_at->format('Y-m-d H:i:s') : '',
                $rule->updated_at ? $rule->updated_at->format('Y-m-d H:i:s') : '',
            ];
        }
        return collect($ruleArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.id'),
            trans('all.label.branch'),
            trans('all.label.currency_amount'),
            trans('all.label.points'),
            trans('all.label.rate') . ' (Points/Currency)',
            trans('all.label.status'),
            trans('all.label.created_at'),
            trans('all.label.updated_at'),
        ];
    }
}
