<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Exports\SalesSummaryReportExport;
use App\Exports\SalesSummaryReportStaffExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\SalesSummaryReportsResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\SaleSummaryReportService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Mpdf\Mpdf;

class SalesSummaryReportController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private SaleSummaryReportService $SaleSummaryReportService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(SaleSummaryReportService $SaleSummaryReportService, CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->companyService = $companyService;
        $this->SaleSummaryReportService = $SaleSummaryReportService;
        $this->themeService  = $themeService;
        $this->middleware(['permission:sales-summary-report'])->only('index', 'export', 'pdf');
    }

    public function salesSummaryReport(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SalesSummaryReportsResource::collection($this->SaleSummaryReportService->saleSummaryReportList($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesSummaryStaffReport(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SalesSummaryReportsResource::collection($this->SaleSummaryReportService->saleSummaryReportByStaffList($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesSummaryReportExport(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new SalesSummaryReportExport($this->SaleSummaryReportService, $request), 'Sales-summary-report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesSummaryReportPdf(PaginateRequest $request): mixed
    {
        try {
            $company = $this->companyService->list();
            $theme_logo   = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
            $copyright   = Settings::group('site')->get('site_copyright');
            $orders = $this->SaleSummaryReportService->saleSummaryReportList($request);

            $fromDate = $request->get('from_date')
                ? \Carbon\Carbon::parse($request->get('from_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
                : \Carbon\Carbon::now()->startOfDay()->format('m/d/Y, h:i A');
            $toDate = $request->get('to_date')
                ? \Carbon\Carbon::parse($request->get('to_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
                : \Carbon\Carbon::now()->endOfDay()->format('m/d/Y, h:i A');

            $html = view('pdf.sales_summary_report', compact('company', 'theme_logo', 'orders', 'copyright', 'fromDate', 'toDate'))->render();

            $mpdf = new Mpdf([
                'format' => 'A4',
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 15,
                'margin_right' => 15,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
            ]);
            $mpdf->WriteHTML($html);

            return response()->stream(
                fn() => print($mpdf->Output('', 'S')),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="sales_summary_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesSummaryReportByStaffExport(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new SalesSummaryReportStaffExport($this->SaleSummaryReportService, $request), 'Sales-summary-report-Staff.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesSummaryReportByStaffPDF(PaginateRequest $request): mixed
    {
        try {
            $company = $this->companyService->list();
            $theme_logo   = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
            $copyright   = Settings::group('site')->get('site_copyright');
            $orders = $this->SaleSummaryReportService->saleSummaryReportList($request);

            $fromDate = $request->get('from_date')
                ? \Carbon\Carbon::parse($request->get('from_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
                : \Carbon\Carbon::now()->startOfDay()->format('m/d/Y, h:i A');
            $toDate = $request->get('to_date')
                ? \Carbon\Carbon::parse($request->get('to_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
                : \Carbon\Carbon::now()->endOfDay()->format('m/d/Y, h:i A');

            $html = view('pdf.sales_summary_staff_report', compact('company', 'theme_logo', 'orders', 'copyright', 'fromDate', 'toDate'))->render();

            $mpdf = new Mpdf([
                'format' => 'A4',
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 15,
                'margin_right' => 15,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
            ]);
            $mpdf->WriteHTML($html);

            return response()->stream(
                fn() => print($mpdf->Output('', 'S')),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="sales_summary_staff_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
