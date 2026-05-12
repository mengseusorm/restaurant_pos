<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Mpdf\Mpdf;
use App\Models\Branch;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Services\CompanyService;
use App\Services\ThemeService;
use App\Services\TherapistProfileService;
use App\Exports\ServiceReportExport;
use App\Http\Resources\ServiceReportResource;
use App\Models\ThemeSetting;
use Smartisan\Settings\Facades\Settings;

class ServiceReportController extends AdminController
{
    private TherapistProfileService $therapistProfileService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(
        TherapistProfileService $therapistProfileService,
        CompanyService $companyService,
        ThemeService $themeService
    ) {
        parent::__construct();
        $this->therapistProfileService = $therapistProfileService;
        $this->companyService = $companyService;
        $this->themeService = $themeService;
        $this->middleware(['permission:service-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ServiceReportResource::collection(
                $this->therapistProfileService->therapistProfileReport($request)
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(
                new ServiceReportExport($this->therapistProfileService, $request),
                'Service-Report.xlsx'
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function pdf(PaginateRequest $request): mixed
    {
        try {
            $company    = $this->companyService->list();
            $theme_logo = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
            $copyright  = Settings::group('site')->get('site_copyright');
            $reports    = $this->therapistProfileService->therapistProfileReport($request);
            $branch     = Branch::find(auth()->user()->branch_id);

            if ($request->get('from_date')) {
                $fromDate = Carbon::parse($request->get('from_date'))->format('m/d/Y, h:i A');
            } else {
                $fromDate = Carbon::now()->subDay()->startOfDay();
                if ($branch && $branch->open_time) {
                    $time = explode(':', $branch->open_time);
                    $fromDate->setTime((int)$time[0], (int)$time[1], 0);
                }
                $fromDate = $fromDate->format('m/d/Y, h:i A');
            }

            if ($request->get('to_date')) {
                $toDate = Carbon::parse($request->get('to_date'))->format('m/d/Y, h:i A');
            } else {
                $toDate = Carbon::now()->startOfDay();
                if ($branch && $branch->close_time) {
                    $time = explode(':', $branch->close_time);
                    $toDate->setTime((int)$time[0], (int)$time[1], 59);
                } else {
                    $toDate->endOfDay();
                }
                $toDate = $toDate->format('m/d/Y, h:i A');
            }

            $html = view(
                'pdf.service_report',
                compact('company', 'theme_logo', 'reports', 'copyright', 'fromDate', 'toDate', 'branch')
            )->render();

            $mpdf = new Mpdf([
                'format'           => 'A4',
                'margin_top'       => 10,
                'margin_bottom'    => 20,
                'margin_left'      => 15,
                'margin_right'     => 15,
                'autoScriptToLang' => true,
                'autoLangToFont'   => true,
            ]);
            $mpdf->WriteHTML($html);

            return response()->stream(
                fn() => print($mpdf->Output('', 'S')),
                200,
                [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="service_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
