<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\BranchTrendReportService;
use App\Services\CompanyService;
use App\Exports\BranchTrendReportExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BranchTrendReportResource;
use App\Http\Requests\PaginateRequest;
use App\Models\ThemeSetting;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Smartisan\Settings\Facades\Settings;
use Illuminate\Support\Facades\Log;

class BranchTrendReportController extends AdminController
{
    private BranchTrendReportService $branchTrendReportService;
    private CompanyService $companyService;

    public function __construct(BranchTrendReportService $branchTrendReportService, CompanyService $companyService)
    {
        parent::__construct();
        $this->branchTrendReportService = $branchTrendReportService;
        $this->companyService = $companyService;
        $this->middleware(['permission:branch-trend-report'])->only('index', 'trendData', 'summaryData', 'export', 'pdf');
    }

    public function index(Request $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\JsonResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new BranchTrendReportResource($this->branchTrendReportService->getTrendReportData($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function trendData(Request $request): \Illuminate\Http\Response | array | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ['data' => $this->branchTrendReportService->getBranchTrendData($request)];
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function summaryData(Request $request): JsonResponse
    {
        try {
            $result = $this->branchTrendReportService->getBranchSummaryData($request);
            return response()->json($result);
        } catch (Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(Request $request)
    {
        $result = $this->branchTrendReportService->getBranchSummaryData($request);
        $summary = $result['branches'];
        $availableCurrencies = $result['available_currencies'];
        $company = $this->companyService->list();
        $fileName = "branch_trend_report_" . now()->format('Y_m_d_H_i_s') . ".xlsx";
        
        return Excel::download(new BranchTrendReportExport($summary, $company, $availableCurrencies), $fileName);
    }

    public function pdf(Request $request)
    {
        try {
            \Log::info('PDF export started with request: ' . json_encode($request->all()));
            
            $result = $this->branchTrendReportService->getBranchSummaryData($request);
            $summary = $result['branches'];
            $availableCurrencies = $result['available_currencies'];
            $monthsArray = $result['months_array'];
            $company = $this->companyService->list();
            
            \Log::info('PDF data prepared', [
                'summary_count' => count($summary),
                'currencies' => $availableCurrencies,
                'months_count' => count($monthsArray)
            ]);
            
            $pdf = Pdf::loadView('admin.report.branch-trend-report-pdf', compact('summary', 'company', 'availableCurrencies', 'monthsArray'));
            $fileName = "branch_trend_report_" . now()->format('Y_m_d_H_i_s') . ".pdf";
            
            \Log::info('PDF generated successfully');
            
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            \Log::error('PDF export error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'PDF generation failed: ' . $e->getMessage()], 500);
        }
    }
}
