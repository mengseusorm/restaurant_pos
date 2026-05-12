<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DailySaleSummaryReportExport;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\DailySaleReportSummaryResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\DailySaleSummaryReportService;
use App\Services\ThemeService;
use Exception;
use Smartisan\Settings\Facades\Settings;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class DailySaleSummaryReportController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private DailySaleSummaryReportService $dailySaleSummaryReportService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(DailySaleSummaryReportService $dailySaleSummaryReportService,CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->dailySaleSummaryReportService = $dailySaleSummaryReportService;
        $this->companyService= $companyService;
        $this->themeService  = $themeService;
        $this->middleware(['permission:daily-sale-summary-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return DailySaleReportSummaryResource::collection($this->dailySaleSummaryReportService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], status: 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new DailySaleSummaryReportExport($this->dailySaleSummaryReportService, $request, $this->companyService), 'Daily-Sale-Report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function pdf(PaginateRequest $request):mixed
    {
        try {
           $company = $this->companyService->list();
           $theme_logo   = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
           $copyright   = Settings::group('site')->get('site_copyright');
           $items = $this->dailySaleSummaryReportService->list($request);

           // Get branch open_time and close_time
           $branch = \App\Models\Branch::find(auth()->user()->branch_id);

           if ($request->get('from_date')) {
               $fromDate = \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($request->get('from_date')));
           } else {
               // Default to yesterday with branch open_time
               $fromDate = \Carbon\Carbon::now()->subDay()->startOfDay();
               if ($branch && $branch->open_time) {
                   $time = explode(':', $branch->open_time);
                   $fromDate->setTime((int)$time[0], (int)$time[1], 0);
               }
               $fromDate = \App\Libraries\AppLibrary::datetime($fromDate);
           }

           if ($request->get('to_date')) {
               $toDate = \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($request->get('to_date')));
           } else {
               // Default to today with branch close_time
               $toDate = \Carbon\Carbon::now()->startOfDay();
               if ($branch && $branch->close_time) {
                   $time = explode(':', $branch->close_time);
                   $toDate->setTime((int)$time[0], (int)$time[1], 59);
               } else {
                   $toDate->endOfDay();
               }
               $toDate = \App\Libraries\AppLibrary::datetime($toDate);
           }

           $html = view('pdf.daily_sale_summary_report', compact('company', 'theme_logo', 'items', 'copyright', 'fromDate', 'toDate'))->render();

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
                   'Content-Disposition' => 'attachment; filename="daily_sale_report.pdf"',
               ]
           );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
