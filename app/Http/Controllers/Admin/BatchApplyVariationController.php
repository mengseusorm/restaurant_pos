<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\BatchApplyVariationService;
use App\Http\Requests\BatchApplyVariationRequest;

class BatchApplyVariationController extends AdminController
{
    public BatchApplyVariationService $batchApplyVariationService;

    public function __construct(BatchApplyVariationService $batchApplyVariationService)
    {
        parent::__construct();
        $this->batchApplyVariationService = $batchApplyVariationService;
        $this->middleware(['permission:settings']);
    }

    /**
     * Get items with attributes for batch apply interface
     */
    public function index() : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $data = $this->batchApplyVariationService->getItemsWithAttributes();
            return response($data, 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Batch apply variations to items
     */
    public function apply(BatchApplyVariationRequest $request) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $result = $this->batchApplyVariationService->batchApply($request);
            return response($result, 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Clear all item variations
     */
    public function clearAll() : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $result = $this->batchApplyVariationService->clearAllVariations();
            return response($result, 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Update item price
     */
    public function updateItemPrice(\Illuminate\Http\Request $request, $itemId) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
            ]);

            $result = $this->batchApplyVariationService->updateItemPrice($itemId, $request->input('price'));
            return response($result, 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
