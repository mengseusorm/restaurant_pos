<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\SimpleOrderResource;
use Exception; 
use App\Services\StockReportService;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\StockRecordResource;
use App\Http\Resources\StockReportResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\StockRecordService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class StockReportController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private StockReportService $stockReportService; 
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(StockReportService $stockReportService, CompanyService $companyService)
    { 
        $this->stockReportService = $stockReportService; 
        $this->companyService     = $companyService; 
    }

    public function index(
        PaginateRequest $request
    ) 
    : \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try { 
            return StockReportResource::collection($this->stockReportService->list($request));
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new StockReportExport($this->stockReportService, $request), 'Stock-Report.xlsx');
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
           $items = $this->stockReportService->list($request);


           $pdf = Pdf::loadView('pdf.stock_report', compact('company', 'theme_logo', 'items', 'copyright') )
            ->setPaper('a4');
                return response()->stream(
                    fn() => print($pdf->output()),
                    200,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="stock_report.pdf"',
                    ]
                ); 

        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}