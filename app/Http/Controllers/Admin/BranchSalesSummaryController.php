<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Http\Requests\PaginateRequest;
use App\Services\CompanyService;
use App\Services\ThemeService;
use App\Http\Resources\BranchSalesSummaryResource;
use App\Libraries\AppLibrary;
use App\Exports\BranchSalesSummaryExport;
use App\Services\BranchSalesSummaryService;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BranchSalesSummaryController extends AdminController
{
    private BranchSalesSummaryService $branchSalesSummaryService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(
        BranchSalesSummaryService $branchSalesSummaryService, 
        CompanyService $companyService, 
        ThemeService $themeService
    ) {
        parent::__construct();
        $this->branchSalesSummaryService = $branchSalesSummaryService;
        $this->companyService = $companyService;
        $this->themeService = $themeService;
        $this->middleware(['permission:branch-sales-summary-report'])->only(
            'index', 'export', 'pdf'
        );
    }

    /**
     * Get branch sales summary report data
     */
    public function index(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->normalizeDateFilters($request);
            $validated = $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date'
            ]);

            Log::info('Branch sales summary report requested', [
                'branch_id' => $validated['branch_id'],
                'from_date' => $validated['from_date'] ?? null,
                'to_date' => $validated['to_date'] ?? null,
                'user_id' => auth()->id()
            ]);
            $reportData = $this->branchSalesSummaryService->generateReport($validated);

            Log::info('Branch sales summary report data generated', [
                'report_data' => $reportData,
                'user_id' => auth()->id()
            ]);
            
            return ['data' => $reportData];

        } catch (Exception $exception) {
            Log::error('Error generating branch sales summary report');
            Log::error($exception->getMessage());

            return response([
                'status' => false, 
                'message' => $exception->getMessage()
            ], 422);
        }
    }

    /**
     * Export branch sales summary report to Excel
     */
    public function export(Request $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->normalizeDateFilters($request);
            $validated = $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date'
            ]);

            $reportData = $this->branchSalesSummaryService->generateReport($validated);
            
            return Excel::download(
                new BranchSalesSummaryExport($reportData), 
                'branch-sales-summary-report.xlsx'
            );

        } catch (Exception $exception) {
            return response([
                'status' => false, 
                'message' => $exception->getMessage()
            ], 422);
        }
    }

    /**
     * Export branch sales summary report to PDF
     */
    public function pdf(Request $request): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $this->normalizeDateFilters($request);
            $validated = $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date'
            ]);

            $reportData = $this->branchSalesSummaryService->generateReport($validated);
            $company = $this->companyService->list();
            
            $pdf = Pdf::loadView('admin.reports.branch-sales-summary', [
                'company' => $company,
                'reportData' => $reportData,
                'filters' => $validated
            ]);

            return $pdf->download('branch-sales-summary-report.pdf');

        } catch (Exception $exception) {
            return response([
                'status' => false, 
                'message' => $exception->getMessage()
            ], 422);
        }
    }

    private function normalizeDateFilters(Request $request): void
    {
        $dates = [];

        if ($request->filled('from_date')) {
            $dates['from_date'] = AppLibrary::filterDateTime($request->get('from_date'))->toDateTimeString();
        }

        if ($request->filled('to_date')) {
            $dates['to_date'] = AppLibrary::filterDateTime($request->get('to_date'))->toDateTimeString();
        }

        if ($dates) {
            $request->merge($dates);
        }
    }
}
