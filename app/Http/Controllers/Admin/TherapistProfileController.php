<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\TherapistProfile;
use Illuminate\Http\Request;
use App\Services\TherapistProfileService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TherapistProfileRequest;
use App\Http\Resources\TherapistProfileResource;

class TherapistProfileController extends AdminController
{
    private TherapistProfileService $therapistProfileService;

    public function __construct(TherapistProfileService $therapistProfileService)
    {
        parent::__construct();
        $this->therapistProfileService = $therapistProfileService;
        $this->middleware(['permission:therapist_profiles_create'])->only('store');
        $this->middleware(['permission:therapist_profiles_edit'])->only('update', 'changeStatus');
        $this->middleware(['permission:therapist_profiles_delete'])->only('destroy');
        $this->middleware(['permission:therapist_profiles_show'])->only('show');
    }

    public function index(PaginateRequest $request)
    {
        try {
            return TherapistProfileResource::collection($this->therapistProfileService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(TherapistProfileRequest $request)
    {
        try {
            return new TherapistProfileResource($this->therapistProfileService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(TherapistProfile $therapistProfile)
    {
        try {
            return new TherapistProfileResource($this->therapistProfileService->show($therapistProfile));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(TherapistProfileRequest $request, TherapistProfile $therapistProfile)
    {
        try {
            return new TherapistProfileResource($this->therapistProfileService->update($request, $therapistProfile));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(TherapistProfile $therapistProfile)
    {
        try {
            $this->therapistProfileService->destroy($therapistProfile);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(Request $request, TherapistProfile $therapistProfile)
    {
        try {
            $request->validate(['status' => 'required|in:available,busy,away']);
            return new TherapistProfileResource(
                $this->therapistProfileService->changeStatus($therapistProfile, $request->status)
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function verifyByCode(Request $request)
    {
        try {
            $request->validate([
                'id'          => 'required|integer',
                'verify_code' => 'required|string',
            ]);
            $therapist = $this->therapistProfileService->verifyByCode(
                (int) $request->id,
                $request->verify_code
            );
            return new TherapistProfileResource($therapist);
        } catch (Exception $exception) {
            $code = $exception->getCode();
            $httpCode = ($code >= 400 && $code < 600) ? $code : 422;
            return response(['status' => false, 'message' => $exception->getMessage()], $httpCode);
        }
    }
}
