<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerBeverageStorageRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\CustomerBeverageStorageResource;
use App\Models\CustomerBeverageStorage;
use App\Services\CustomerBeverageStorageService;
use Exception;
use Illuminate\Http\Request;

class CustomerBeverageStorageController extends AdminController
{
    private CustomerBeverageStorageService $customerBeverageStorageService;

    public function __construct(CustomerBeverageStorageService $customerBeverageStorageService)
    {
        parent::__construct();
        $this->customerBeverageStorageService = $customerBeverageStorageService;
        $this->middleware(['permission:customer_beverage_storage_create'])->only('store');
        $this->middleware(['permission:customer_beverage_storage_edit'])->only('update', 'markAsClaimed', 'markAsDisposed');
        $this->middleware(['permission:customer_beverage_storage_delete'])->only('destroy');
        $this->middleware(['permission:customer_beverage_storage_show'])->only('show');
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return CustomerBeverageStorageResource::collection($this->customerBeverageStorageService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(
        CustomerBeverageStorageRequest $request
    ): \Illuminate\Http\Response | CustomerBeverageStorageResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new CustomerBeverageStorageResource($this->customerBeverageStorageService->store($request->validated()));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        CustomerBeverageStorage $customerBeverageStorage
    ): \Illuminate\Http\Response | CustomerBeverageStorageResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new CustomerBeverageStorageResource($this->customerBeverageStorageService->show($customerBeverageStorage));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        CustomerBeverageStorageRequest $request,
        CustomerBeverageStorage $customerBeverageStorage
    ): \Illuminate\Http\Response | CustomerBeverageStorageResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new CustomerBeverageStorageResource($this->customerBeverageStorageService->update($request, $customerBeverageStorage));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        CustomerBeverageStorage $customerBeverageStorage
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->customerBeverageStorageService->destroy($customerBeverageStorage);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function markAsClaimed(
        Request $request,
        CustomerBeverageStorage $customerBeverageStorage
    ): \Illuminate\Http\Response | CustomerBeverageStorageResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new CustomerBeverageStorageResource($this->customerBeverageStorageService->markAsClaimed($request, $customerBeverageStorage));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function markAsDisposed(
        Request $request,
        CustomerBeverageStorage $customerBeverageStorage
    ): \Illuminate\Http\Response | CustomerBeverageStorageResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new CustomerBeverageStorageResource($this->customerBeverageStorageService->markAsDisposed($request, $customerBeverageStorage));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
