<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DailySaleReportExport;
use App\Http\Resources\SimpleOrderResource;
use Exception;
use App\Services\DailySaleReportService;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\DailySaleResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;

class DailySaleReportController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private DailySaleReportService $dailySaleReportService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(DailySaleReportService $dailySaleReportService,CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->dailySaleReportService = $dailySaleReportService;
        $this->companyService= $companyService;
        $this->themeService  = $themeService;
        $this->middleware(['permission:sales-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return DailySaleResource::collection($this->dailySaleReportService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], status: 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new DailySaleReportExport($this->dailySaleReportService, $request), 'Daily-Sale-Report.xlsx');
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
           $items = $this->dailySaleReportService->list($request);

           $fromDate = $request->get('from_date')
               ? \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($request->get('from_date'))->setTimezone('UTC'))
               : \App\Libraries\AppLibrary::datetime(\Carbon\Carbon::now()->startOfDay());
           $toDate = $request->get('to_date')
               ? \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($request->get('to_date'))->setTimezone('UTC'))
               : \App\Libraries\AppLibrary::datetime(\Carbon\Carbon::now()->endOfDay());

           $html = view('pdf.daily_sale_report', compact('company', 'theme_logo', 'items', 'copyright', 'fromDate', 'toDate'))->render();

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
