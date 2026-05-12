<?php

namespace App\Imports;

use App\Libraries\EnumAppLibrary;
use App\Models\ItemCategory;
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
use DB;
use App\Models\Branch;


class ItemCategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        $branch_id = $this->getBranchId($this->sanitizeInput($row['branch'])); 
        if($branch_id)
            return new ItemCategory([
                'branch_id' => $branch_id,
                'name' => $this->sanitizeInput($row['name'] ?? ''),
                'slug' => Str::slug($this->sanitizeInput($row['name'])),
                'status' => EnumAppLibrary::itemStatus($row['status']),
                'description' => $this->sanitizeInput($row['description'] ?? ''),
            ]);
    }

    public function rules(): array
    {
        return [
            'branch' => ['required', 'string'],
            'name'        => [
                'required',
                'string',
                'max:190',
                Rule::unique("item_categories", "name")
            ],
            'description' => ['nullable', 'string', 'max:900'],
            'status'      => ['nullable', 'string'],
        ];
    }

    private function sanitizeInput($value): array|bool|string
    {
        return mb_convert_encoding(trim($value), 'UTF-8', 'UTF-8');
    }

    private function getBranchId($branch): int|null
    {
        $branch = Branch::where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($branch) . '%')->first();
        if ($branch) {
            return $branch->id;
        }
        return null;
    }
}
