<?php

namespace App\Imports;

use App\Enums\Status;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Tax;
use App\Rules\IniAmount;
use App\Libraries\EnumAppLibrary;
use App\Models\Branch;
use App\Models\KitchenPrinter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;


class ItemImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        $category_id = $this->getCategoryId($this->sanitizeInput($row['category']));
        $branch_id = $this->getBranchId($this->sanitizeInput($row['branch'])); 
        if($category_id == null) {
            $new_categories = new ItemCategory([
                'name'        => $row['category'],
                'slug'        => Str::slug($row['category']),
                'description' => '',
                'status'      => Status::ACTIVE,
                'branch_id'   => $branch_id == null ? 1 : $branch_id,
                'sort'        => 100
            ]);
            $new_categories->save();
            $category_id = $new_categories->id;
        }

        if ($category_id && $branch_id) {
            return new Item([
                'name' => $this->sanitizeInput($row['name'] ?? ''),
                'name_kh' => $this->sanitizeInput($row['name_kh'] ?? $row['name kh'] ?? ''),
                'name_cn' => $this->sanitizeInput($row['name_cn'] ?? $row['name cn'] ?? ''),
                'name_en' => $this->sanitizeInput($row['name_en'] ?? $row['name en'] ?? ''),
                'item_code' => $this->sanitizeInput($row['item_code'] ?? $row['item code'] ?? ''),
                'item_category_id' => $category_id,
                'branch_id' => $branch_id,
                'slug' => Str::slug($this->sanitizeInput($row['name'])),
                'tax_id' => $this->getTaxId($row['tax'] ?? 0),
                'item_type' => EnumAppLibrary::itemType($this->sanitizeInput($row['item_type'] ?? $row['item type'] ?? '')),
                'price' => $row['price'] ?? 0,
                'is_featured' => EnumAppLibrary::itemFeature($row['featured'] ?? 'no'),
                'description' => $this->sanitizeInput($row['description'] ?? ''),
                'caution' => $this->sanitizeInput($row['caution'] ?? ''),
                'status' => EnumAppLibrary::itemStatus($row['status'] ?? 'active'),
                'barcode' => $this->sanitizeInput($row['barcode'] ?? ''),
                'manage_stock' => EnumAppLibrary::yesNoStatus($row['manage_stock'] ?? $row['manage stock'] ?? 'no'),
                'kitchen_printer_id' => $this->getPrinterId($row['printer_name'] ?? $row['printer name'] ?? ''),
                'is_print_menu' => EnumAppLibrary::yesNoStatus($row['is_print_menu'] ?? 'yes'),
                'is_print_label' => EnumAppLibrary::yesNoStatus($row['is_print_label'] ?? 'no'),
                'can_input_custom_name' => EnumAppLibrary::yesNoStatus($row['can_input_custom_name'] ?? 'no'),
                'can_input_custom_unit_price' => EnumAppLibrary::yesNoStatus($row['can_input_custom_unit_price'] ?? 'no'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:190',
                Rule::unique("items", "name")->whereNull('deleted_at')
            ],
            'name_kh' => ['nullable', 'string', 'max:190'],
            'name kh' => ['nullable', 'string', 'max:190'],
            'name_cn' => ['nullable', 'string', 'max:190'],
            'name cn' => ['nullable', 'string', 'max:190'],
            'name_en' => ['nullable', 'string', 'max:190'],
            'name en' => ['nullable', 'string', 'max:190'],
            'item_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique("items", "item_code")->whereNull('deleted_at')
            ],
            'item code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique("items", "item_code")->whereNull('deleted_at')
            ],
            'category' => ['required', 'string'],
            'branch' => ['required', 'string'],
            'tax' => ['nullable', 'numeric'],
            'item_type' => ['nullable'],
            'item type' => ['nullable'],
            'price' => ['required', new IniAmount()],
            'featured' => ['nullable'],
            'description' => ['nullable', 'string', 'max:5000'],
            'caution' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'max:24'],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique("items", "barcode")->whereNull('deleted_at')
            ],
            'manage_stock' => ['nullable', 'string'],
            'manage stock' => ['nullable', 'string'],
            'printer_name' => ['nullable', 'string'],
            'printer name' => ['nullable', 'string'],
            'is_print_menu' => ['nullable', 'string'],
            'is_print_label' => ['nullable', 'string'],
            'can_input_custom_name' => ['nullable', 'string'],
            'can_input_custom_unit_price' => ['nullable', 'string'],
        ];
    }

    private function sanitizeInput($value): array|bool|string
    {
        return mb_convert_encoding(trim($value), 'UTF-8', 'UTF-8');
    }

    private function getTaxId($tax_rate): int|null
    {
        $tax = Tax::where('tax_rate', $tax_rate)->first();
        if ($tax) {
            return $tax->id;
        }
        return null;
    }

    private function getCategoryId($categoryName): int|null
    {
        $category = ItemCategory::where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($categoryName) . '%')->first();
        if ($category) {
            return $category->id;
        }
        return null;
    }

    private function getBranchId($branch): int|null
    {
        $branch = Branch::where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($branch) . '%')->first();
        if ($branch) {
            return $branch->id;
        }
        return null;
    }

    private function getPrinterId($printerName): int|null
    {
        if (empty($printerName)) {
            return null;
        }

        // Trim and sanitize the printer name
        $cleanPrinterName = trim($printerName);

        // Try exact match first
        $printer = KitchenPrinter::where('name', $cleanPrinterName)->first();

        if ($printer) {
            return $printer->id;
        }

        // Try case-insensitive exact match
        $printer = KitchenPrinter::whereRaw('LOWER(name) = LOWER(?)', [$cleanPrinterName])->first();
        if ($printer) {
            return $printer->id;
        }

        // Try partial match as fallback
        $printer = KitchenPrinter::where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($cleanPrinterName) . '%')->first();

        if ($printer) {
            return $printer->id;
        }
        return null;
    }

}
