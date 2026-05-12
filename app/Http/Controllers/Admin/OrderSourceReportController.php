<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderSourceExport;
use Exception;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\OrderSourceReportResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\OrderSourceReportService;
use App\Services\ThemeService;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Smartisan\Settings\Facades\Settings;

class OrderSourceReportController extends AdminController
{

    private OrderSourceReportService $orderSourceReportService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(OrderSourceReportService $orderSourceReportService, CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->orderSourceReportService = $orderSourceReportService;
        $this->companyService= $companyService;
        $this->themeService  = $themeService;
        $this->middleware(['permission:order-source-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return OrderSourceReportResource::collection($this->orderSourceReportService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new OrderSourceExport($this->orderSourceReportService,$request),'Order-Type-Report.xlsx');
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
            $items =   $this->orderSourceReportService->list($request);

            $fromDate = $request->get('from_date')
                ? \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($request->get('from_date'))->setTimezone('UTC'))
                : \App\Libraries\AppLibrary::datetime(\Carbon\Carbon::now()->startOfDay());
            $toDate = $request->get('to_date')
                ? \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($request->get('to_date'))->setTimezone('UTC'))
                : \App\Libraries\AppLibrary::datetime(\Carbon\Carbon::now()->endOfDay());

            $html = view('pdf.order_source_report', compact('company', 'theme_logo', 'items', 'copyright', 'fromDate', 'toDate'))->render();

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
                    'Content-Disposition' => 'attachment; filename="order_source_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
