<?php

namespace App\Services;

use App\Enums\CustomerBeverageStorageStatus;
use App\Models\CustomerBeverageStorage;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerBeverageStorageService
{
    public object $customerBeverageStorage;
    protected array $customerBeverageStorageFilter = [
        'customer_name',
        'customer_phone',
        'beverage_name',
        'storage_code',
        'status',
        'branch_id',
    ];

    /**
     * @throws Exception
     */
    public function list($request)
    {
        try {
            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType = $request->get('order_type') ?? 'desc';

            return CustomerBeverageStorage::with('branch', 'createdBy')
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->customerBeverageStorageFilter)) {
                            if ($key === 'status') {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                    
                    // Handle date range filtering
                    if (isset($requests['from_date']) && $requests['from_date']) {
                        $query->whereDate('store_date', '>=', $requests['from_date']);
                    }
                    if (isset($requests['to_date']) && $requests['to_date']) {
                        $query->whereDate('store_date', '<=', $requests['to_date']);
                    }
                })
                ->orderBy($orderColumn, $orderType)
                ->$method($methodValue);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            
            // Generate unique storage code
            $data['storage_code'] = $this->generateStorageCode();
            
        // Auto-generate storage_code
        $data['storage_code'] = $this->generateStorageCode();

        // Set original_quantity from quantity if not provided
        if (!isset($data['original_quantity']) || empty($data['original_quantity'])) {
            $data['original_quantity'] = $data['quantity'] ?? 0;
        }

        // Auto-calculate expiry_date if not provided (store_date + 14 days)
        if (!isset($data['expiry_date']) || empty($data['expiry_date'])) {
            $data['expiry_date'] = Carbon::parse($data['store_date'])->addDays(14)->format('Y-m-d');
        }            // Set original_quantity same as quantity if not provided
            if (!isset($data['original_quantity'])) {
                $data['original_quantity'] = $data['quantity'];
            }
            
            $customerBeverageStorage = CustomerBeverageStorage::create($data);
            
            if (isset($data['image'])) {
                $customerBeverageStorage->addMedia($data['image'])->toMediaCollection('customer_beverage_storage');
            }
            
            return $customerBeverageStorage;
        });
    }

    public function update($request, $customerBeverageStorage)
    {
        try {
            DB::transaction(function () use ($request, $customerBeverageStorage) {
                $customerBeverageStorage->update($request->validated());
                
                if ($request->hasFile('image')) {
                    $customerBeverageStorage->clearMediaCollection('customer_beverage_storage');
                    $customerBeverageStorage->addMedia($request->file('image'))->toMediaCollection('customer_beverage_storage');
                }
                
                $this->customerBeverageStorage = $customerBeverageStorage;
            });
            return $this->customerBeverageStorage;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy($customerBeverageStorage): void
    {
        try {
            DB::transaction(function () use ($customerBeverageStorage) {
                $customerBeverageStorage->clearMediaCollection('customer_beverage_storage');
                $customerBeverageStorage->delete();
            });
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show($customerBeverageStorage): CustomerBeverageStorage
    {
        try {
            return $customerBeverageStorage->load('branch', 'createdBy');
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function markAsClaimed($request, $customerBeverageStorage)
    {
        try {
            DB::transaction(function () use ($request, $customerBeverageStorage) {
                $updateData = [
                    'status' => CustomerBeverageStorageStatus::CLAIMED,
                    'claimed_date' => $request->claimed_date ?? now()
                ];
                
                // Update quantity if provided
                if ($request->quantity) {
                    $updateData['quantity'] = 0; // All claimed
                }
                
                if ($request->notes) {
                    $updateData['notes'] = $request->notes;
                }
                
                $customerBeverageStorage->update($updateData);
                $this->customerBeverageStorage = $customerBeverageStorage;
            });
            return $this->customerBeverageStorage;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function markAsDisposed($request, $customerBeverageStorage)
    {
        try {
            DB::transaction(function () use ($request, $customerBeverageStorage) {
                $updateData = [
                    'status' => CustomerBeverageStorageStatus::DISPOSED,
                    'disposed_date' => $request->disposed_date ?? now()
                ];
                
                if ($request->disposed_reason) {
                    $updateData['disposed_reason'] = $request->disposed_reason;
                }
                
                if ($request->notes) {
                    $updateData['notes'] = $request->notes;
                }
                
                $customerBeverageStorage->update($updateData);
                $this->customerBeverageStorage = $customerBeverageStorage;
            });
            return $this->customerBeverageStorage;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Generate unique storage code
     */
    private function generateStorageCode(): string
    {
        $date = Carbon::now()->format('Ymd');
        $lastCode = CustomerBeverageStorage::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastCode) {
            $lastSequence = (int) substr($lastCode->storage_code, -3);
            $sequence = $lastSequence + 1;
        }
        
        return 'STG-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
