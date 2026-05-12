<?php
namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodSevice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends AdminController
{
    public PaymentMethodSevice $paymentMethodSevice;

    public function __construct(PaymentMethodSevice $paymentMethodSevice)
    {
        parent::__construct();
        $this->paymentMethodSevice = $paymentMethodSevice;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy');
    }

    public function index(PaginateRequest $request
    ) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $paymentMethods = $this->paymentMethodSevice->list($request);
            
            // Check if last_updated parameter was provided and no payment methods were found
            if ($request->has('last_updated') && $paymentMethods->isEmpty()) {
                return response(['status' => true, 'message' => 'No payment methods updated', 'has_updates' => false], 200);
            }
            
            return PaymentMethodResource::collection($paymentMethods);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function listOnlinePayment(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return PaymentMethodResource::collection($this->paymentMethodSevice->listOnlinePayment($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function listTablePayment(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return PaymentMethodResource::collection($this->paymentMethodSevice->listTablePayment($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function show(PaymentMethod $paymentMethod
    ) : PaymentMethodResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new PaymentMethodResource($this->paymentMethodSevice->show($paymentMethod));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(PaymentMethodRequest $request) : PaymentMethodResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try { 
            return new PaymentMethodResource($this->paymentMethodSevice->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function update(
        PaymentMethodRequest $request,
        PaymentMethod $paymentMethod
    ) : PaymentMethodResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new PaymentMethodResource($this->paymentMethodSevice->update($request, $paymentMethod));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(PaymentMethod $paymentMethod
    ) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try { 
            $this->paymentMethodSevice->destroy($paymentMethod);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
