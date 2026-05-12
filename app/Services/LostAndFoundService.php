<?php

namespace App\Services;

use App\Enums\LostAndFoundStatus;
use App\Models\LostAndFound;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LostAndFoundService
{
    public object $lostAndFound;
    protected array $lostAndFoundFilter = [
        'item_name',
        'item_code',
        'found_location',
        'customer_name',
        'customer_phone',
        'status',
        'branch_id',
        'found_date'
    ];

    protected array $exceptFilter = [
        'excepts'
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

            return LostAndFound::with('branch', 'createdBy')
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->lostAndFoundFilter)) {
                            if ($key === 'found_date') {
                                $query->whereDate($key, $request);
                            } elseif ($key === 'status') {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                    
                    // Handle date range filtering
                    if (isset($requests['from_date']) && $requests['from_date']) {
                        $query->whereDate('found_date', '>=', $requests['from_date']);
                    }
                    if (isset($requests['to_date']) && $requests['to_date']) {
                        $query->whereDate('found_date', '<=', $requests['to_date']);
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
            
            $lostAndFound = LostAndFound::create($data);
            
            if (isset($data['image'])) {
                $lostAndFound->addMedia($data['image'])->toMediaCollection('lost_and_found');
            }
            
            return $lostAndFound;
        });
    }
    public function update($request, $lostAndFound)
    {
        try {
            DB::transaction(function () use ($request, $lostAndFound) {
                $lostAndFound->update($request->validated());

                if ($request->image) {
                    $lostAndFound->clearMediaCollection('lost_and_found');
                    $lostAndFound->addMedia($request->image)->toMediaCollection('lost_and_found');
                }

                $this->lostAndFound = $lostAndFound;
            });
            return $this->lostAndFound;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy($lostAndFound)
    {
        try {
            DB::transaction(function () use ($lostAndFound) {
                $lostAndFound->clearMediaCollection('lost_and_found');
                $lostAndFound->delete();
            });
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show($lostAndFound): LostAndFound
    {
        try {
            return $lostAndFound->load('branch', 'createdBy');
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function markAsClaimed($request, $lostAndFound)
    {
        try {
            DB::transaction(function () use ($request, $lostAndFound) {
                $updateData = [
                    'status' => LostAndFoundStatus::CLAIMED,
                    'claimed_date' => $request->claimed_date ?? now()
                ];
                
                if ($request->claimed_by) {
                    $updateData['claimed_by'] = $request->claimed_by;
                }
                
                if ($request->notes) {
                    $updateData['notes'] = $request->notes;
                }
                
                $lostAndFound->update($updateData);
                $this->lostAndFound = $lostAndFound;
            });
            return $this->lostAndFound;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function markAsDisposed($request, $lostAndFound)
    {
        try {
            DB::transaction(function () use ($request, $lostAndFound) {
                $updateData = [
                    'status' => LostAndFoundStatus::DISPOSED,
                    'disposal_date' => $request->disposal_date ?? now()
                ];
                
                if ($request->notes) {
                    $updateData['notes'] = $request->notes;
                }
                
                $lostAndFound->update($updateData);
                $this->lostAndFound = $lostAndFound;
            });
            return $this->lostAndFound;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
