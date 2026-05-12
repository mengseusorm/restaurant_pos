<?php

namespace App\Services;

use App\Enums\Status;
use App\Http\Requests\PaymentMethodRequest; 
use App\Http\Requests\PaginateRequest; 
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;
use App\Libraries\QueryExceptionLibrary;
use Carbon\Carbon;

class PaymentMethodSevice
{
    protected array $branchFilter = [
        'name',
        'email',
        'phone',
        'latitude',
        'longitude',
        'city',
        'state',
        'zip_code',
        'address',
        'status',
        'close_business_day_time',
        'current_business_day',
        'last_updated'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_number') ?? 'asc';    
            
            $payment = PaymentMethod::with('supportedCurrencies')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->branchFilter)) {
                        if ($key == 'last_updated') {
                            // Filter payment methods updated after the provided timestamp
                            $query->where('updated_at', '>', $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            })->orderBy('order_number', 'asc')->$method(
                $methodValue
            ); 
            return $payment;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function listOnlinePayment(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_number') ?? 'asc';

            $payment = PaymentMethod::where('show_online_payment', Status::ACTIVE)
            ->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                if (in_array($key, $this->branchFilter)) {
                    $query->where($key, 'like', '%' . $request . '%');
                }
                }
            })
            ->orderBy('order_number', 'asc')
            ->$method($methodValue);

            return $payment;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function listTablePayment(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_number') ?? 'asc';

            $payment = PaymentMethod::where('show_table_order_payment', Status::ACTIVE)
            ->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                if (in_array($key, $this->branchFilter)) {
                    $query->where($key, 'like', '%' . $request . '%');
                }
                }
            })
            ->orderBy('order_number', 'asc')
            ->$method($methodValue);

            return $payment;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(PaymentMethodRequest $request)
    {
        try {    
            $paymentMethod = PaymentMethod::create($request->validated());
            
            // Attach supported currencies if provided
            if ($request->has('supported_currencies') && is_array($request->supported_currencies)) {
                $paymentMethod->supportedCurrencies()->sync($request->supported_currencies);
            }
            
            if ($request->hasFile('pos_static_qr_code')) {
                $paymentMethod->addMediaFromRequest('pos_static_qr_code')
                    ->toMediaCollection('pos_static_qr_code');
            }
            
            if ($request->hasFile('logo')) {
                $paymentMethod->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }
            
            return $paymentMethod; 
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        try {
            $updatedPaymentMethod = tap($paymentMethod)->update($request->validated());
            
            // Sync supported currencies if provided
            if ($request->has('supported_currencies')) {
                if (is_array($request->supported_currencies)) {
                    $paymentMethod->supportedCurrencies()->sync($request->supported_currencies);
                } else {
                    // If null or empty, detach all
                    $paymentMethod->supportedCurrencies()->sync([]);
                }
            }
            
            if ($request->hasFile('pos_static_qr_code')) {
                // Clear existing media
                $paymentMethod->clearMediaCollection('pos_static_qr_code');
                
                // Add new media
                $paymentMethod->addMediaFromRequest('pos_static_qr_code')
                    ->toMediaCollection('pos_static_qr_code');
            }
            
            if ($request->hasFile('logo')) {
                // Clear existing media
                $paymentMethod->clearMediaCollection('logo');
                
                // Add new media
                $paymentMethod->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }
            
            return $updatedPaymentMethod;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(PaymentMethod $paymentMethod): void
    {
        try {
            $paymentMethod->delete(); 
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422); 
        }
    }

    /**
     * @throws Exception
     */
    public function show(PaymentMethod $branch): PaymentMethod
    {
        try {
            return $branch->load('supportedCurrencies');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
