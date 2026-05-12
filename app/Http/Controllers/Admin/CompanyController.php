<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Services\CompanyService;
use Exception;

class CompanyController extends AdminController
{
    public CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        parent::__construct();
        $this->companyService = $companyService;
        $this->middleware(['permission:settings'])->only('update');
    }

    public function index(\Illuminate\Http\Request $request) : \Illuminate\Http\Response | CompanyResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $company = $this->companyService->list($request);
            
            // Check if last_updated parameter was provided and company was not updated
            if ($request->has('last_updated') && $company && isset($company->updated_at)) {
                if ($company->updated_at->toIso8601String() <= $request->get('last_updated')) {
                    return response(['status' => true, 'message' => 'No company info updated', 'has_updates' => false], 200);
                }
            }
            
            return new CompanyResource($company);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(CompanyRequest $request) : \Illuminate\Http\Response | CompanyResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new CompanyResource($this->companyService->update($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
