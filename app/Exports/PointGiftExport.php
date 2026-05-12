<?php

namespace App\Exports;

use App\Services\PointGiftService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PointGiftExport implements FromCollection, WithHeadings
{
    public PointGiftService $pointGiftService;
    public PaginateRequest $request;

    public function __construct(PointGiftService $pointGiftService, $request)
    {
        $this->pointGiftService = $pointGiftService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $giftArray = [];
        $gifts = $this->pointGiftService->getForExport();

        foreach ($gifts as $gift) {
            $stockStatus = $gift->is_unlimited_stock 
                ? 'Unlimited' 
                : ($gift->is_in_stock 
                    ? "In Stock ({$gift->remaining_stock} remaining)" 
                    : 'Out of Stock');

            $availabilityStatus = !$gift->is_active 
                ? 'Inactive' 
                : (!$gift->item || $gift->item->trashed() 
                    ? 'Item Not Available' 
                    : (!$gift->is_in_stock 
                        ? 'Out of Stock' 
                        : 'Available'));

            $giftArray[] = [
                $gift->id,
                $gift->branch ? $gift->branch->name : 'N/A',
                $gift->item ? $gift->item->name : 'Item Not Found',
                $gift->item ? $gift->item->price : 0,
                $gift->required_points,
                $gift->stock_limit ?? 'Unlimited',
                $gift->redeemed_count,
                $gift->remaining_stock == -1 ? 'Unlimited' : $gift->remaining_stock,
                $stockStatus,
                $availabilityStatus,
                $gift->points_saved,
                $gift->is_active ? trans('all.label.active') : trans('all.label.inactive'),
                $gift->created_at ? $gift->created_at->format('Y-m-d H:i:s') : '',
                $gift->updated_at ? $gift->updated_at->format('Y-m-d H:i:s') : '',
            ];
        }
        return collect($giftArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.id'),
            trans('all.label.branch'),
            trans('all.label.item_name'),
            trans('all.label.item_price'),
            trans('all.label.required_points'),
            trans('all.label.stock_limit'),
            trans('all.label.redeemed_count'),
            trans('all.label.remaining_stock'),
            trans('all.label.stock_status'),
            trans('all.label.availability_status'),
            trans('all.label.points_saved'),
            trans('all.label.status'),
            trans('all.label.created_at'),
            trans('all.label.updated_at'),
        ];
    }
}
