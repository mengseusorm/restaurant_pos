<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KitchenPrinterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'ip'             => $this->ip,
            'port'           => $this->port,
            'printer_type'   => $this->printer_type,
            'printer_method' => $this->printer_method,
            'branch_id'      => $this->branch_id,
            'branch'         => new BranchMinimalResource($this->branch),
            'printer_server' => $this->printer_server,
            'label'          => $this->label,
            'print_copies'   => $this->print_copies
        ];
    }
}