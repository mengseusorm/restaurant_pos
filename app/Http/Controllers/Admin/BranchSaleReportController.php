<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BranchSaleReportExport; 
use App\Http\Resources\SimpleOrderResource;
use Exception;
use App\Services\OrderService;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\BranchSaleReportResource;
use App\Models\ThemeSetting; 
use App\Services\BranchSaleReportService;
use App\Services\CompanyService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
class BranchSaleReportController extends AdminController
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     private BranchSaleReportService $branchSaleReportService;
     private CompanyService $companyService;
     private ThemeService $themeService;
 
     public function __construct(BranchSaleReportService $branchSaleReportService,CompanyService $companyService, ThemeService $themeService)
     {
         parent::__construct();
         $this->branchSaleReportService = $branchSaleReportService;
         $this->companyService= $companyService;
         $this->themeService  = $themeService;
         $this->middleware(['permission:branch-sale-report'])->only('index', 'export', 'pdf');
     }
 
     public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
     {
        try {
            return BranchSaleReportResource::collection($this->branchSaleReportService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
     }
     
     public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
     {
         try {
             return Excel::download(new BranchSaleReportExport($this->branchSaleReportService, $request), 'branch-sales-report.xlsx');
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
            $items = $this->branchSaleReportService->list($request);
 
 
            $pdf = Pdf::loadView('pdf.branch_sales_report', compact('company', 'theme_logo', 'items', 'copyright') )
                ->setPaper('a4');
                return response()->stream(
                    fn() => print($pdf->output()),
                    200,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="branch_sales_report.pdf"',
                    ]
                );
        
        
         } catch (Exception $exception) {
             return response(['status' => false, 'message' => $exception->getMessage()], 422);
         }
     }
}
