<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LostAndFoundRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\LostAndFoundResource;
use App\Models\LostAndFound;
use App\Services\LostAndFoundService;
use Exception;
use Illuminate\Http\Request;

class LostAndFoundController extends AdminController
{
    private LostAndFoundService $lostAndFoundService;

    public function __construct(LostAndFoundService $lostAndFoundService)
    {
        parent::__construct();
        $this->lostAndFoundService = $lostAndFoundService;
        $this->middleware(['permission:lost_and_found_create'])->only('store');
        $this->middleware(['permission:lost_and_found_edit'])->only('update', 'markAsClaimed', 'markAsDisposed');
        $this->middleware(['permission:lost_and_found_delete'])->only('destroy');
        $this->middleware(['permission:lost_and_found_show'])->only('show');
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return LostAndFoundResource::collection($this->lostAndFoundService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(
        LostAndFoundRequest $request
    ): \Illuminate\Http\Response | LostAndFoundResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new LostAndFoundResource($this->lostAndFoundService->store($request->validated()));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        LostAndFound $lostAndFound
    ): \Illuminate\Http\Response | LostAndFoundResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new LostAndFoundResource($this->lostAndFoundService->show($lostAndFound));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        LostAndFoundRequest $request,
        LostAndFound $lostAndFound
    ): \Illuminate\Http\Response | LostAndFoundResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new LostAndFoundResource($this->lostAndFoundService->update($request, $lostAndFound));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        LostAndFound $lostAndFound
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->lostAndFoundService->destroy($lostAndFound);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function markAsClaimed(
        Request $request,
        LostAndFound $lostAndFound
    ): \Illuminate\Http\Response | LostAndFoundResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new LostAndFoundResource($this->lostAndFoundService->markAsClaimed($request, $lostAndFound));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function markAsDisposed(
        Request $request,
        LostAndFound $lostAndFound
    ): \Illuminate\Http\Response | LostAndFoundResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new LostAndFoundResource($this->lostAndFoundService->markAsDisposed($request, $lostAndFound));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
