<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\TherapistProfileService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServiceReportExport implements FromCollection
{
    public TherapistProfileService $therapistProfileService;
    public PaginateRequest $request;

    public function __construct(TherapistProfileService $therapistProfileService, $request)
    {
        $this->therapistProfileService = $therapistProfileService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $rows = [];

        $branch = \App\Models\Branch::find(auth()->user()->branch_id);

        if ($this->request->get('from_date')) {
            $fromDate = \Carbon\Carbon::parse($this->request->get('from_date'))->format('m/d/Y, h:i A');
        } else {
            $fromDate = \Carbon\Carbon::now()->subDay()->startOfDay();
            if ($branch && $branch->open_time) {
                $time = explode(':', $branch->open_time);
                $fromDate->setTime((int)$time[0], (int)$time[1], 0);
            }
            $fromDate = $fromDate->format('m/d/Y, h:i A');
        }

        if ($this->request->get('to_date')) {
            $toDate = \Carbon\Carbon::parse($this->request->get('to_date'))->format('m/d/Y, h:i A');
        } else {
            $toDate = \Carbon\Carbon::now()->startOfDay();
            if ($branch && $branch->close_time) {
                $time = explode(':', $branch->close_time);
                $toDate->setTime((int)$time[0], (int)$time[1], 59);
            } else {
                $toDate->endOfDay();
            }
            $toDate = $toDate->format('m/d/Y, h:i A');
        }

        // Title
        $rows[] = ['Service Report', '', '', '', ''];
        $rows[] = ['From: ' . $fromDate, 'To: ' . $toDate, '', '', ''];
        $rows[] = ['', '', '', '', ''];

        // Header
        $rows[] = [
            trans('all.label.therapist'),
            trans('all.label.total_order'),
            trans('all.label.total_customer'),
            trans('all.label.total_hours'),
            trans('all.label.total_revenue'),
        ];

        $reports = $this->therapistProfileService->therapistProfileReport($this->request);

        $totalOrders    = 0;
        $totalCustomers = 0;
        $totalHours     = 0;
        $totalRevenue   = 0;

        foreach ($reports as $report) {
            $totalOrders    += $report->total_orders ?? 0;
            $totalCustomers += $report->total_customers ?? 0;
            $totalHours     += $report->total_hours ?? 0;
            $totalRevenue   += $report->total_revenue ?? 0;

            $rows[] = [
                $report->therapist_name ?: 'N/A',
                $report->total_orders ?? 0,
                $report->total_customers ?? 0,
                number_format((float)($report->total_hours ?? 0), 2),
                $this->formatRevenue($report->total_revenue ?? 0, $branch),
            ];
        }

        $rows[] = [
            trans('all.label.total'),
            $totalOrders,
            $totalCustomers,
            number_format($totalHours, 2),
            $this->formatRevenue($totalRevenue, $branch),
        ];

        return collect($rows);
    }

    private function formatRevenue($amount, $branch): string
    {
        if ($branch) {
            return AppLibrary::branchCurrencyAmountFormat($amount, $branch);
        }

        return AppLibrary::flatAmountFormat($amount);
    }
}
