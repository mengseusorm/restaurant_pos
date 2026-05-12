<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ItemsReportDetailExport;
use Exception;
use App\Services\ItemService;
use App\Exports\ItemsReportExport;
use App\Http\Resources\ItemResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ItemReportResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;

class ItemsReportController extends AdminController
{

    private ItemService $itemService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(ItemService $itemService,CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->itemService = $itemService;
        $this->companyService= $companyService;
        $this->themeService  = $themeService;
        $this->middleware(['permission:items-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ItemReportResource::collection($this->itemService->itemReport($request));
        } catch (Exception $exception) {
            Log::error('Error fetching items report: ' . $exception->getMessage());
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ItemsReportExport($this->itemService, $request), 'Item-Report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function exportDetail(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ItemsReportDetailExport($this->itemService, $request, true), 'Item-Report-Detail.xlsx');
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
            $items =   $this->itemService->itemReport($request);

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

            $html = view('pdf.items_report', compact('company', 'theme_logo', 'items', 'copyright', 'fromDate', 'toDate'))->render();            $mpdf = new Mpdf([
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
                    'Content-Disposition' => 'attachment; filename="items_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
