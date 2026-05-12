<?php

namespace App\Exports;

use App\Services\PointUsageRuleService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PointUsageRuleExport implements FromCollection, WithHeadings
{
    public PointUsageRuleService $pointUsageRuleService;
    public PaginateRequest $request;

    public function __construct(PointUsageRuleService $pointUsageRuleService, $request)
    {
        $this->pointUsageRuleService = $pointUsageRuleService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $ruleArray = [];
        $rules = $this->pointUsageRuleService->getForExport();

        foreach ($rules as $rule) {
            $usageType = match($rule->usage_type) {
                'deduct_order' => 'Deduct from Order',
                'exchange_gift' => 'Exchange for Gift',
                default => ucfirst(str_replace('_', ' ', $rule->usage_type)),
            };

            $usageRange = $rule->max_point_usage 
                ? "From {$rule->min_point_usage} to {$rule->max_point_usage} points"
                : "Minimum {$rule->min_point_usage} points";

            $ruleArray[] = [
                $rule->id,
                $rule->branch ? $rule->branch->name : 'N/A',
                $rule->name,
                $usageType,
                $rule->point_to_currency,
                $rule->min_point_usage,
                $rule->max_point_usage ?? 'Unlimited',
                $usageRange,
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
            trans('all.label.name'),
            trans('all.label.usage_type'),
            trans('all.label.point_to_currency'),
            trans('all.label.min_point_usage'),
            trans('all.label.max_point_usage'),
            trans('all.label.usage_range'),
            trans('all.label.status'),
            trans('all.label.created_at'),
            trans('all.label.updated_at'),
        ];
    }
}
