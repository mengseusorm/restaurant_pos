<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'event' => $this->event,
            'properties' => $this->properties,
            'batch_uuid' => $this->batch_uuid,
            'branch_id' => $this->branch_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'causer' => $this->whenLoaded('causer', function () {
                return [
                    'id' => $this->causer->id,
                    'name' => $this->causer->name,
                    'email' => $this->causer->email,
                    'type' => class_basename($this->causer),
                ];
            }),
            
            'subject' => $this->whenLoaded('subject', function () {
                if (!$this->subject) {
                    return null;
                }
                
                $subject = [
                    'id' => $this->subject->id,
                    'type' => class_basename($this->subject),
                ];
                
                // Add type-specific fields
                switch (class_basename($this->subject)) {
                    case 'Order':
                        $subject['order_serial_no'] = $this->subject->order_serial_no ?? null;
                        $subject['status'] = $this->subject->status ?? null;
                        $subject['total'] = $this->subject->total ?? null;
                        break;
                        
                    case 'User':
                        $subject['name'] = $this->subject->name ?? null;
                        $subject['email'] = $this->subject->email ?? null;
                        break;
                        
                    case 'Member':
                        $subject['name'] = $this->subject->name ?? null;
                        $subject['phone'] = $this->subject->phone ?? null;
                        $subject['point_balance'] = $this->subject->point_balance ?? null;
                        break;
                        
                    case 'Item':
                        $subject['name'] = $this->subject->name ?? null;
                        $subject['sku'] = $this->subject->sku ?? null;
                        break;
                        
                    case 'DiningTable':
                        $subject['name'] = $this->subject->name ?? null;
                        $subject['status'] = $this->subject->status ?? null;
                        break;
                }
                
                return $subject;
            }),
            
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),
            
            // Formatted fields for display
            'formatted_created_at' => $this->created_at?->diffForHumans(),
            'formatted_properties' => $this->getFormattedProperties(),
            'activity_icon' => $this->getActivityIcon(),
            'activity_color' => $this->getActivityColor(),
        ];
    }
    
    /**
     * Get formatted properties for display
     */
    private function getFormattedProperties(): array
    {
        if (!$this->properties || $this->properties->isEmpty()) {
            return [];
        }
        
        $formatted = [];
        
        foreach ($this->properties as $key => $value) {
            $formatted[] = [
                'key' => str_replace('_', ' ', ucfirst($key)),
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'raw_key' => $key,
                'raw_value' => $value,
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get activity icon based on log name and event
     */
    private function getActivityIcon(): string
    {
        return match ($this->log_name) {
            'auth' => 'user-check',
            'order' => 'shopping-cart',
            'payment' => 'credit-card',
            'pos' => 'monitor',
            'table_order' => 'table',
            'member' => 'users',
            'inventory' => 'package',
            'system' => 'settings',
            default => 'activity'
        };
    }
    
    /**
     * Get activity color based on log name and event
     */
    private function getActivityColor(): string
    {
        return match ($this->log_name) {
            'auth' => 'blue',
            'order' => 'green',
            'payment' => 'purple',
            'pos' => 'orange',
            'table_order' => 'indigo',
            'member' => 'pink',
            'inventory' => 'yellow',
            'system' => 'gray',
            default => 'gray'
        };
    }
}
