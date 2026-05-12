<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KitchenPrinterRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\KitchenPrinterResource;
use App\Models\kitchenPrinter;
use App\Services\KitchenPrinterService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KitchenPrinterController extends Controller
{
    public KitchenPrinterService $kitchenPrinterService;

    public function __construct(KitchenPrinterService $kitchenPrinterService)
    {
        $this->kitchenPrinterService = $kitchenPrinterService;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy');
    }
    public function show(Request $request, $id) : \Illuminate\Http\Response | KitchenPrinterResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory 
    {
        try {
            $printer = $this->kitchenPrinterService->show($id);
            
            // Check if last_updated parameter was provided and printer exists
            if ($request->has('last_updated') && $printer) {
                // Get updated_at timestamp
                $updatedAt = $printer->updated_at ?? $printer->getAttribute('updated_at');
                
                if ($updatedAt && $updatedAt->toIso8601String() <= $request->get('last_updated')) {
                    return response([
                        'status' => true,
                        'message' => 'No printer updated',
                        'has_updates' => false
                    ], 200);
                }
            }
            
            return new KitchenPrinterResource($printer); 
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function index(PaginateRequest $request, kitchenPrinter $kitchenPrinter): \Illuminate\Http\Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $printers = $this->kitchenPrinterService->list($request, $kitchenPrinter);
            
            // Check if last_updated parameter was provided and no updates found
            if ($request->has('last_updated') && $printers->isEmpty()) {
                return response([
                    'status' => true,
                    'message' => 'No printers updated',
                    'has_updates' => false
                ], 200);
            }
            
            return KitchenPrinterResource::collection($printers);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(KitchenPrinterRequest $request, kitchenPrinter $kitchenPrinter): \Illuminate\Http\Response | KitchenPrinterResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try { 
            return new KitchenPrinterResource($this->kitchenPrinterService->store($request, $kitchenPrinter));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(KitchenPrinterRequest $request, kitchenPrinter $kitchenPrinter): \Illuminate\Http\Response|KitchenPrinterResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new KitchenPrinterResource($this->kitchenPrinterService->update($request, $kitchenPrinter));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        try {
            return $this->kitchenPrinterService->destroy($id);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}