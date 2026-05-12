<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\ItemAttributeVariation;
use App\Services\ItemAttributeVariationService;
use App\Http\Requests\ItemAttributeVariationRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ItemAttributeVariationResource;

class ItemAttributeVariationController extends AdminController
{
    public ItemAttributeVariationService $itemAttributeVariationService;

    public function __construct(ItemAttributeVariationService $itemAttributeVariationService)
    {
        parent::__construct();
        $this->itemAttributeVariationService = $itemAttributeVariationService;
        $this->middleware(['permission:settings'])->only('show', 'store', 'update', 'destroy');
    }

    public function index(PaginateRequest $request
    ) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return ItemAttributeVariationResource::collection($this->itemAttributeVariationService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(ItemAttributeVariation $itemAttributeVariation
    ) : \Illuminate\Http\Response | ItemAttributeVariationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ItemAttributeVariationResource($this->itemAttributeVariationService->show($itemAttributeVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ItemAttributeVariationRequest $request
    ) : \Illuminate\Http\Response | ItemAttributeVariationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ItemAttributeVariationResource($this->itemAttributeVariationService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        ItemAttributeVariationRequest $request,
        ItemAttributeVariation $itemAttributeVariation
    ) : \Illuminate\Http\Response | ItemAttributeVariationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ItemAttributeVariationResource($this->itemAttributeVariationService->update($request, $itemAttributeVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(ItemAttributeVariation $itemAttributeVariation
    ) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->itemAttributeVariationService->destroy($itemAttributeVariation);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
