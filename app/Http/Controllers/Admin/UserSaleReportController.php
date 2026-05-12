<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UserSaleReportExport;
use Exception;
use App\Services\UserSaleReportService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\UserSaleReportResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;

class UserSaleReportController extends AdminController
{
    private UserSaleReportService $userSaleReportService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(UserSaleReportService $userSaleReportService, CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->userSaleReportService = $userSaleReportService;
        $this->companyService = $companyService;
        $this->themeService = $themeService;
        $this->middleware(['permission:user-sales-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return UserSaleReportResource::collection($this->userSaleReportService->list($request));
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new UserSaleReportExport($this->userSaleReportService, $request), 'User-Sales-Report.xlsx');
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function pdf(PaginateRequest $request): mixed
    {
        try {
            $company = $this->companyService->list();
            $theme_logo = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
            $copyright = Settings::group('site')->get('site_copyright');
            $userSales = $this->userSaleReportService->list($request);

            $fromDate = $request->get('from_date')
                ? \Carbon\Carbon::parse($request->get('from_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
                : \Carbon\Carbon::now()->startOfDay()->format('m/d/Y, h:i A');
            $toDate = $request->get('to_date')
                ? \Carbon\Carbon::parse($request->get('to_date'))->setTimezone('UTC')->format('m/d/Y, h:i A')
                : \Carbon\Carbon::now()->endOfDay()->format('m/d/Y, h:i A');

            $html = view('pdf.user_sale_report', compact('company', 'theme_logo', 'userSales', 'copyright', 'fromDate', 'toDate'))->render();

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
                    'Content-Disposition' => 'attachment; filename="user_sale_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
