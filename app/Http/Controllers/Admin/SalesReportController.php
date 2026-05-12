<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\SimpleOrderResource;
use Exception;
use App\Services\OrderService;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\SaleReportResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;

class SalesReportController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private OrderService $orderService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(OrderService $order,CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->orderService = $order;
        $this->companyService= $companyService;
        $this->themeService  = $themeService;
        $this->middleware(['permission:sales-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SaleReportResource::collection($this->orderService->list($request));
            // return SimpleOrderResource::collection($this->orderService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new SalesReportExport($this->orderService, $request), 'Sales-Report.xlsx');
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
           $orders = $this->orderService->list($request);

           $fromDate = $request->get('from_date')
               ? \Carbon\Carbon::parse($request->get('from_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
               : \Carbon\Carbon::now()->startOfDay()->format('m/d/Y, h:i A');
           $toDate = $request->get('to_date')
               ? \Carbon\Carbon::parse($request->get('to_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
               : \Carbon\Carbon::now()->endOfDay()->format('m/d/Y, h:i A');

           $html = view('pdf.sales_report', compact('company', 'theme_logo', 'orders', 'copyright', 'fromDate', 'toDate'))->render();

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
                   'Content-Disposition' => 'attachment; filename="sales_report.pdf"',
               ]
           );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
