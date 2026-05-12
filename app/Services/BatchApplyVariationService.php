<?php

namespace App\Services;

use Exception;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ItemAttributeVariation;
use App\Models\ItemVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\BatchApplyVariationRequest;

class BatchApplyVariationService
{
    /**
     * Get items with their current variations grouped by attributes
     * 
     * @throws Exception
     */
    public function getItemsWithAttributes()
    {
        try {
            // Get all active items with their variations
            $items = Item::with(['variations.itemAttribute', 'category'])
                ->where('status', \App\Enums\Status::ACTIVE)
                ->orderBy('name')
                ->get();

            // Get all active attributes with their variations
            $attributes = ItemAttribute::with(['variations' => function($query) {
                $query->where('status', \App\Enums\Status::ACTIVE);
            }])
            ->where('status', \App\Enums\Status::ACTIVE)
            ->orderBy('name')
            ->get();

            return [
                'items' => $items,
                'attributes' => $attributes
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Batch apply attribute variations to items
     * 
     * @throws Exception
     */
    public function batchApply(BatchApplyVariationRequest $request)
    {
        try {
            $variations = $request->input('variations', []);
            $created = 0;
            $updated = 0;

            DB::transaction(function () use ($variations, &$created, &$updated) {
                foreach ($variations as $variation) {
                    $itemId = $variation['item_id'];
                    $attributeVariationId = $variation['item_attribute_variation_id'];
                    $price = $variation['price'] ?? null;

                    // Get the attribute variation details
                    $attributeVariation = ItemAttributeVariation::with('itemAttribute')->findOrFail($attributeVariationId);
                    
                    // Use the provided price or the attribute variation's default price
                    $finalPrice = $price !== null ? $price : $attributeVariation->price;

                    // Check if this variation already exists for this item
                    $existingVariation = ItemVariation::where('item_id', $itemId)
                        ->where('item_attribute_variation_id', $attributeVariationId)
                        ->first();

                    if ($existingVariation) {
                        // Update existing variation
                        $existingVariation->update([
                            'price' => $finalPrice,
                            'caution' => $attributeVariation->caution,
                            'status' => $attributeVariation->status,
                        ]);
                        $updated++;
                    } else {
                        // Create new variation
                        ItemVariation::create([
                            'item_id' => $itemId,
                            'item_attribute_id' => $attributeVariation->item_attribute_id,
                            'item_attribute_variation_id' => $attributeVariationId,
                            'name' => $attributeVariation->name,
                            'price' => $finalPrice,
                            'caution' => $attributeVariation->caution,
                            'status' => $attributeVariation->status,
                        ]);
                        $created++;
                    }
                }
            });

            return [
                'success' => true,
                'created' => $created,
                'updated' => $updated,
                'total' => count($variations)
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Clear all item variations
     * 
     * @throws Exception
     */
    public function clearAllVariations()
    {
        try {
            DB::beginTransaction();
            
            $deletedCount = ItemVariation::count();
            ItemVariation::query()->delete();
            
            DB::commit();
            
            return [
                'success' => true,
                'deleted' => $deletedCount,
                'message' => 'All item variations cleared successfully'
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Update item price
     * 
     * @throws Exception
     */
    public function updateItemPrice($itemId, $price)
    {
        try {
            $item = Item::findOrFail($itemId);
            
            // Calculate tax amount and price with tax
            $taxRate = $item->tax_rate ?? 0;
            $taxAmount = ($price * $taxRate) / 100;
            $priceWithTax = $price + $taxAmount;

            $item->update([
                'price' => $price,
                'tax_amount' => $taxAmount,
                'price_with_tax' => $priceWithTax,
            ]);

            return [
                'status' => true,
                'message' => 'Item price updated successfully',
                'item' => $item->fresh(['category', 'variations.itemAttribute'])
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
