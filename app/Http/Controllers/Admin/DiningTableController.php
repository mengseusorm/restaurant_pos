<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\DiningTable;
use App\Exports\DiningTableExport;
use App\Services\DiningTableService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\DiningTableRequest;
use App\Http\Requests\DiningTableChangeImageRequest;
use App\Http\Resources\DiningTableResource;
use Illuminate\Support\Facades\Log;

class DiningTableController extends AdminController
{
    private DiningTableService $diningTableService;

    public function __construct(DiningTableService $diningTable)
    {
        parent::__construct();
        $this->diningTableService = $diningTable;
        $this->middleware(['permission:dining-tables'])->only('export', 'changeImage');
        $this->middleware(['permission:dining_tables_create'])->only('store');
        $this->middleware(['permission:dining_tables_edit'])->only('update', 'changeImage');
        $this->middleware(['permission:dining_tables_delete'])->only('destroy');
        $this->middleware(['permission:dining_tables_show'])->only('show');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $diningTables = $this->diningTableService->list($request);

            // Check if last_updated parameter was provided and no dining tables were found
            if ($request->has('last_updated') && $diningTables->isEmpty()) {
                return response(['status' => true, 'message' => 'No dining tables updated', 'has_updates' => false], 200);
            }

            return DiningTableResource::collection($diningTables);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function store(
        DiningTableRequest $request
    ): \Illuminate\Http\Response | DiningTableResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try { 
            return new DiningTableResource($this->diningTableService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        DiningTable $diningTable
    ): \Illuminate\Http\Response | DiningTableResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new DiningTableResource($this->diningTableService->show($diningTable));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        DiningTableRequest $request,
        DiningTable $diningTable
    ): \Illuminate\Http\Response | DiningTableResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new DiningTableResource($this->diningTableService->update($request, $diningTable));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        DiningTable $diningTable
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->diningTableService->destroy($diningTable);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeImage(
        DiningTableChangeImageRequest $request,
        DiningTable $diningTable
    ): \Illuminate\Http\Response | DiningTableResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new DiningTableResource($this->diningTableService->changeImage($request, $diningTable));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    // public function release(
    //     DiningTable $diningTable
    // ): \Illuminate\Http\Response | DiningTableResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
    //     try {
    //         return new DiningTableResource($this->diningTableService->release($diningTable));
    //     } catch (Exception $exception) {
    //         return response(['status' => false, 'message' => $exception->getMessage()], 422);
    //     }
    // }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new DiningTableExport($this->diningTableService, $request), 'Dining-Table.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
