<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'branch_id'          => $this->branch_id,
            'branch'             => $this->branch ? [
                'id'   => $this->branch->id,
                'name' => $this->branch->name
            ] : null,
            'expense_code'       => $this->expense_code,
            'expense_date'       => $this->expense_date ? $this->expense_date->format('Y-m-d') : null,
            'expense_type_id'    => $this->expense_type_id,
            'expense_type'       => $this->expenseType ? [
                'id'   => $this->expenseType->id,
                'name' => $this->expenseType->name
            ] : null,
            'amount'             => $this->amount,
            'payment_method_id'  => $this->payment_method_id,
            'payment_method'     => $this->paymentMethod ? [
                'id'   => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name
            ] : null,
            'description'        => $this->description,
            'receipt_image'      => $this->receipt_image,
            'receipt_preview'    => $this->receipt_preview,
            'paid_to'            => $this->paid_to,
            'reference_no'       => $this->reference_no,
            'recorded_by'        => $this->recorded_by,
            'recorded_by_user'   => $this->recordedBy ? [
                'id'   => $this->recordedBy->id,
                'name' => $this->recordedBy->name
            ] : null,
            'approved_by'        => $this->approved_by,
            'approved_by_user'   => $this->approvedBy ? [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name
            ] : null,
            'status'             => $this->status,
            'is_deleted'         => $this->is_deleted
        ];
    }
}
