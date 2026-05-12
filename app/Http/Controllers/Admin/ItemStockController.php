<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ItemStockExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ItemStockRequest;
use App\Http\Resources\ItemStockResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\ItemStockService;
use App\Services\ThemeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Smartisan\Settings\Facades\Settings;

class ItemStockController extends Controller
{
    public ItemStockService $itemStockService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(ItemStockService $itemStockService,CompanyService $companyService, ThemeService $themeService)
    {
        $this->itemStockService = $itemStockService;
        $this->companyService   = $companyService;
        $this->themeService     = $themeService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return ItemStockResource::collection($this->itemStockService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ItemStockRequest $request): \Illuminate\Http\Response | ItemStockResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ItemStockResource($this->itemStockService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(
        ItemStockRequest $request,$id
    ): \Illuminate\Http\Response | ItemStockResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ItemStockResource($this->itemStockService->update($request, $id));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(
        $id
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->itemStockService->destroy($id);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function sortCategory(
        Request $request
    ) {
        try {
            $this->itemStockService->sortCategory($request);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new ItemStockExport($this->itemStockService, $request), 'ItemStock.xlsx');
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
            $items = $this->itemStockService->list($request);


            $pdf = Pdf::loadView('pdf.stock_warehouse', compact('company', 'theme_logo', 'items', 'copyright') )
                ->setPaper('a4');
                return response()->stream(
                    fn() => print($pdf->output()),
                    200,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="stock_warehouse.pdf"',
                    ]
                );


         } catch (Exception $exception) {
             return response(['status' => false, 'message' => $exception->getMessage()], 422);
         }
     }
}
