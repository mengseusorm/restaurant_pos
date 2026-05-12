<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StockRecordExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\StockRecordRequest;
use App\Http\Resources\StockRecordResource;
use App\Models\StockRecord;
use App\Services\StockRecordService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class StockRecordController extends Controller
{
    private StockRecordService $stockRecordService;

    public function __construct(StockRecordService $stockRecord)
    { 
        $this->stockRecordService = $stockRecord; 
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return StockRecordResource::collection($this->stockRecordService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function store(
        StockRecordRequest $request
    ): \Illuminate\Http\Response | StockRecordResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try { 
            return new StockRecordResource($this->stockRecordService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function storeStockTransfer(
        StockRecordRequest $request
    ){
        try { 
            return StockRecordResource::collection($this->stockRecordService->storeStockTransfer($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        StockRecord $StockRecord
    ): \Illuminate\Http\Response | StockRecordResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new StockRecordResource($this->stockRecordService->show($StockRecord));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        StockRecordRequest $request, 
        $StockRecord
    ): \Illuminate\Http\Response | StockRecordResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try { 
            return new StockRecordResource($this->stockRecordService->update($request, $StockRecord));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        $StockRecord
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->stockRecordService->destroy($StockRecord);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function sortCategory(
        Request $request
    ) {
        try {
            $this->stockRecordService->sortCategory($request);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new StockRecordExport($this->stockRecordService, $request), 'StockRecord.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    // public function pdf(PaginateRequest $request):mixed
    // {
    //     try {
    //        $company = $this->companyService->list();
    //        $theme_logo   = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
    //        $copyright   = Settings::group('site')->get('site_copyright');
    //        $items = $this->dailySaleReportService->list($request);


    //        $pdf = Pdf::loadView('pdf.daily_sale_report', compact('company', 'theme_logo', 'items', 'copyright') )
    //        ->setPaper('a4');
    //         return response()->stream(
    //             fn() => print($pdf->output()),
    //             200,
    //             [
    //                 'Content-Type' => 'application/pdf',
    //                 'Content-Disposition' => 'attachment; filename="daily_sale_report.pdf"',
    //             ]
    //         );


    //     } catch (Exception $exception) {
    //         return response(['status' => false, 'message' => $exception->getMessage()], 422);
    //     }
    // }

    public function cutstock(
        StockRecordRequest $request
    ): \Illuminate\Http\Response | StockRecordResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {   
            return new StockRecordResource($this->stockRecordService->cutstock($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    


 
}
