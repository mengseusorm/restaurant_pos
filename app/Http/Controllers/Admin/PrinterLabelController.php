<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PrintLabelRequest;
use App\Http\Resources\PrintLabelResource;
use App\Models\PrintLabelSetting;
use App\Services\PrintLabelService;
use Exception;
use Illuminate\Support\Facades\Log;

class PrinterLabelController extends AdminController
{ 
    public PrintLabelService $printLabelService;

    public function __construct(PrintLabelService $printLabelService)
    {
        parent::__construct();
        $this->printLabelService = $printLabelService;
        $this->middleware(['permission:settings'])->only('update');
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $printLabelSettings = $this->printLabelService->list($request);
            
            // Check if last_updated parameter was provided and no settings were found
            if ($request->has('last_updated') && $printLabelSettings->isEmpty()) {
                return response(['status' => true, 'message' => 'No print label settings updated', 'has_updates' => false], 200);
            }
            
            return PrintLabelResource::collection($printLabelSettings);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }  

    public function store(PrintLabelRequest $request
    ) : PrintLabelResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new PrintLabelResource($this->printLabelService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function update(
        PrintLabelRequest $request,
        PrintLabelSetting $printLabelSetting
    ) : PrintLabelResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new PrintLabelResource($this->printLabelService->update($request, $printLabelSetting));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }  
    public function show(PrintLabelSetting $printLabelSetting): \Illuminate\Http\Response|PrintLabelResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new PrintLabelResource($this->printLabelService->show($printLabelSetting));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function destroy(PrintLabelSetting $printLabelSetting
    ) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try { 
            $this->printLabelService->destroy($printLabelSetting);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
} 
