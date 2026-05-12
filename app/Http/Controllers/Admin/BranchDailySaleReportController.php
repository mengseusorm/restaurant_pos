<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\BranchDailySaleReportService;
use App\Services\CompanyService;
use App\Exports\BranchDailySaleReportExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class BranchDailySaleReportController extends AdminController
{
    private BranchDailySaleReportService $branchDailySaleReportService;
    private CompanyService $companyService;

    public function __construct(BranchDailySaleReportService $branchDailySaleReportService, CompanyService $companyService)
    {
        parent::__construct();
        $this->middleware(['permission:branch-daily-sale-report'])->only('index', 'export', 'pdf');
        $this->branchDailySaleReportService = $branchDailySaleReportService;
        $this->companyService = $companyService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->branchDailySaleReportService->getDailySaleData($request);
            return response()->json($result);
        } catch (Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(Request $request)
    {
        try {
            Log::info('Excel export started with request: ' . json_encode($request->all()));

            $result = $this->branchDailySaleReportService->getDailySaleData($request);
            $summary = $result['branches'];
            $dateRange = $result['date_range'];
            $availableCurrencies = $result['available_currencies'];
            $company = $this->companyService->list();

            Log::info('Excel export data prepared', [
                'summary_count' => count($summary),
                'date_range_count' => count($dateRange),
                'currencies_count' => count($availableCurrencies),
                'currencies' => $availableCurrencies
            ]);

            $fileName = "branch_daily_sale_report_" . now()->format('Y_m_d_H_i_s') . ".xlsx";

            return Excel::download(new BranchDailySaleReportExport($summary, $company, $dateRange, $availableCurrencies), $fileName);
        } catch (Exception $exception) {
            Log::error('Excel export error: ' . $exception->getMessage());
            Log::error('Stack trace: ' . $exception->getTraceAsString());
            return response()->json(['error' => 'Excel export failed: ' . $exception->getMessage()], 500);
        }
    }

    public function pdf(Request $request)
    {
        try {
            Log::info('PDF export started with request: ' . json_encode($request->all()));

            $result = $this->branchDailySaleReportService->getDailySaleData($request);
            $summary = $result['branches'];
            $dateRange = $result['date_range'];
            $availableCurrencies = $result['available_currencies'];
            $company = $this->companyService->list();

            Log::info('PDF data prepared', [
                'summary_count' => count($summary),
                'date_range_count' => count($dateRange),
                'currencies_count' => count($availableCurrencies)
            ]);

            $pdf = Pdf::loadView('admin.report.branch-daily-sale-report-pdf', compact('summary', 'company', 'dateRange', 'availableCurrencies'));
            $fileName = "branch_daily_sale_report_" . now()->format('Y_m_d_H_i_s') . ".pdf";

            Log::info('PDF generated successfully');

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('PDF export error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'PDF generation failed: ' . $e->getMessage()], 500);
        }
    }
}
