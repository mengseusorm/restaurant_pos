<template>
    <div class="table-item"
         :style="tableStyle"
         :class="tableClasses"
         @click.stop="handleClick"
         @mousedown="startDrag"
         @dblclick="handleDoubleClick">
        
        <!-- Table Shape -->
        <div class="table-shape" :class="shapeClass" :style="tableShapeStyle">
            <!-- Table Photo Background -->
            <div v-if="table.table_photo" class="table-photo-overlay"></div>
            
            <!-- Table Number -->
            <div class="table-number">{{ table.name }}</div>
            
            <!-- Occupancy Info -->
            <div class="occupancy-info" v-if="table.size > 0">
                <span class="guests">{{ table.current_guests || 0 }}/{{ table.size }}</span>
            </div>
        </div>
        
        <!-- Status Indicator -->
        <div class="table-status" :class="statusClass"></div>
        
        <!-- Resize Handle (Edit Mode) -->
        <div v-if="editMode && selected" class="resize-handle" @mousedown.stop="startResize"></div>
        
        <!-- Action Buttons (Non-Edit Mode) -->
        <div v-if="!editMode && (selected || table.is_occupied)" class="action-buttons">
            <button v-if="table.current_order_id" 
                    @click.stop="$emit('viewOrder', table)"
                    class="action-btn view-order"
                    :title="$t('label.view_order')">
                <i class="lab lab-eye"></i>
            </button>
            <button v-if="table.current_order_id" 
                    @click.stop="$emit('release', table.id)"
                    class="action-btn release"
                    :title="$t('label.release_table')">
                <i class="lab lab-logout"></i>
            </button>
            <button v-if="!table.current_order_id"
                    @click.stop="showGuestCounter = !showGuestCounter"
                    class="action-btn guests"
                    :title="$t('label.set_guests')">
                <i class="lab lab-user-plus"></i>
            </button>
        </div>
        
        <!-- Guest Counter -->
        <div v-if="showGuestCounter" class="guest-counter" @click.stop>
            <div class="guest-counter-controls">
                <button @click="decrementGuests" class="guest-btn">-</button>
                <span class="guest-count">{{ guestCount }}</span>
                <button @click="incrementGuests" class="guest-btn">+</button>
            </div>
            <div class="guest-counter-actions">
                <button @click="saveGuests" class="save-btn">{{ $t('label.save') }}</button>
                <button @click="cancelGuests" class="cancel-btn">{{ $t('label.cancel') }}</button>
            </div>
        </div>
        
        <!-- Order Info Tooltip -->
        <div v-if="table.orders && !editMode" class="order-tooltip">
            <div class="order-info">
                <div class="order-time">{{ formatOrderTime(table.orders.created_at) }}</div>
                <div class="order-total">${{ table.orders.total || 0 }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import appService from '../../../services/appService';

export default {
    name: "DiningTableComponent",
    props: {
        table: {
            type: Object,
            required: true
        },
        editMode: {
            type: Boolean,
            default: false
        },
        selected: {
            type: Boolean,
            default: false
        },
        useScaledPosition: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            isDragging: false,
            isResizing: false,
            dragStart: { x: 0, y: 0 },
            initialPosition: { x: 0, y: 0 },
            initialSize: { width: 0, height: 0 },
            showGuestCounter: false,
            guestCount: 0
        }
    },
    computed: {
        tableStyle() {
            const positionX = this.useScaledPosition && this.table.scaledPositionX !== undefined 
                ? this.table.scaledPositionX 
                : (this.table.position_x || 0);
            const positionY = this.useScaledPosition && this.table.scaledPositionY !== undefined 
                ? this.table.scaledPositionY 
                : (this.table.position_y || 0);
            const width = this.useScaledPosition && this.table.scaledWidth !== undefined 
                ? this.table.scaledWidth 
                : (this.table.width || 80);
            const height = this.useScaledPosition && this.table.scaledHeight !== undefined 
                ? this.table.scaledHeight 
                : (this.table.height || 80);
                
            return {
                left: `${positionX}px`,
                top: `${positionY}px`,
                width: `${width}px`,
                height: `${height}px`,
                backgroundColor: this.table.color || '#3B82F6',
                transform: `rotate(${this.table.rotation || 0}deg)`,
                zIndex: this.selected ? 1000 : (this.table.is_occupied ? 100 : 1)
            };
        },
        
        tableClasses() {
            return {
                selected: this.selected,
                occupied: this.table.is_occupied,
                available: !this.table.is_occupied,
                dragging: this.isDragging,
                resizing: this.isResizing
            };
        },
        
        shapeClass() {
            return {
                'shape-rectangle': this.table.shape === 'rectangle' || !this.table.shape,
                'shape-circle': this.table.shape === 'circle',
                'shape-square': this.table.shape === 'square'
            };
        },
        
        statusClass() {
            return {
                'status-occupied': this.table.is_occupied,
                'status-available': !this.table.is_occupied
            };
        },

        tableShapeStyle() {
            const style = {};
            
            if (this.table.table_photo) {
                style.backgroundImage = `url(${this.table.table_photo})`;
                style.backgroundSize = 'cover';
                style.backgroundPosition = 'center';
                style.backgroundRepeat = 'no-repeat';
            }
            
            return style;
        }
    },
    
    watch: {
        table: {
            handler(newTable) {
                this.guestCount = newTable.current_guests || 0;
            },
            immediate: true
        }
    },
    
    methods: {
        handleClick() {
            this.$emit('select', this.table);
        },
        
        handleDoubleClick() {
            if (!this.editMode && !this.table.is_occupied) {
                this.showGuestCounter = true;
            }
        },
        
        startDrag(event) {
            if (!this.editMode) return;
            
            this.isDragging = true;
            this.dragStart = {
                x: event.clientX,
                y: event.clientY
            };
            this.initialPosition = {
                x: this.table.position_x || 0,
                y: this.table.position_y || 0
            };
            
            document.addEventListener('mousemove', this.drag);
            document.addEventListener('mouseup', this.stopDrag);
        },
        
        drag(event) {
            if (!this.isDragging) return;
            
            const deltaX = event.clientX - this.dragStart.x;
            const deltaY = event.clientY - this.dragStart.y;
            
            const newX = Math.max(0, this.initialPosition.x + deltaX);
            const newY = Math.max(0, this.initialPosition.y + deltaY);
            
            const updatedTable = {
                ...this.table,
                position_x: newX,
                position_y: newY
            };
            
            this.$emit('update', updatedTable);
        },
        
        stopDrag() {
            this.isDragging = false;
            document.removeEventListener('mousemove', this.drag);
            document.removeEventListener('mouseup', this.stopDrag);
        },
        
        startResize(event) {
            if (!this.editMode) return;
            
            event.preventDefault();
            this.isResizing = true;
            this.dragStart = {
                x: event.clientX,
                y: event.clientY
            };
            this.initialSize = {
                width: this.table.width || 80,
                height: this.table.height || 80
            };
            
            document.addEventListener('mousemove', this.resize);
            document.addEventListener('mouseup', this.stopResize);
        },
        
        resize(event) {
            if (!this.isResizing) return;
            
            const deltaX = event.clientX - this.dragStart.x;
            const deltaY = event.clientY - this.dragStart.y;
            
            const newWidth = Math.max(40, this.initialSize.width + deltaX);
            const newHeight = Math.max(40, this.initialSize.height + deltaY);
            
            const updatedTable = {
                ...this.table,
                width: newWidth,
                height: newHeight
            };
            
            this.$emit('update', updatedTable);
        },
        
        stopResize() {
            this.isResizing = false;
            document.removeEventListener('mousemove', this.resize);
            document.removeEventListener('mouseup', this.stopResize);
        },
        
        incrementGuests() {
            if (this.guestCount < this.table.size) {
                this.guestCount++;
            }
        },
        
        decrementGuests() {
            if (this.guestCount > 0) {
                this.guestCount--;
            }
        },
        
        saveGuests() {
            this.$emit('updateGuests', this.table.id, this.guestCount);
            this.showGuestCounter = false;
        },
        
        cancelGuests() {
            this.guestCount = this.table.current_guests || 0;
            this.showGuestCounter = false;
        },
        
        formatOrderTime(timestamp) {
            if (!timestamp) return '';
            return appService.timeAgo(timestamp);
        }
    }
}
</script>

<style scoped>
.table-item {
    position: absolute;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    user-select: none;
}

.table-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.table-item.selected {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.table-item.occupied {
    border-color: #ef4444;
}

.table-item.available {
    border-color: #10b981;
}

.table-shape {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    position: relative;
}

.shape-rectangle {
    border-radius: 4px;
}

.shape-circle {
    border-radius: 50%;
}

.shape-square {
    border-radius: 0;
}

.table-number {
    font-size: 14px;
    line-height: 1;
    margin-bottom: 2px;
}

.occupancy-info {
    font-size: 10px;
    opacity: 0.9;
}

.table-status {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-occupied {
    background-color: #ef4444;
}

.status-available {
    background-color: #10b981;
}

.resize-handle {
    position: absolute;
    bottom: -5px;
    right: -5px;
    width: 10px;
    height: 10px;
    background: #3b82f6;
    border: 1px solid white;
    cursor: se-resize;
    border-radius: 2px;
}

.action-buttons {
    position: absolute;
    top: -35px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 4px;
    background: rgba(0, 0, 0, 0.8);
    padding: 4px;
    border-radius: 4px;
}

.action-btn {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 2px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.action-btn.view-order {
    background-color: #3b82f6;
}

.action-btn.release {
    background-color: #ef4444;
}

.action-btn.guests {
    background-color: #10b981;
}

.guest-counter {
    position: absolute;
    top: -60px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    /* z-index: 1001; */
}

.guest-counter-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.guest-btn {
    width: 24px;
    height: 24px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.guest-count {
    min-width: 20px;
    text-align: center;
    font-weight: bold;
    color: #374151;
}

.guest-counter-actions {
    display: flex;
    gap: 4px;
}

.save-btn, .cancel-btn {
    padding: 4px 8px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.save-btn {
    background-color: #10b981;
    color: white;
}

.cancel-btn {
    background-color: #6b7280;
    color: white;
}

.order-tooltip {
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    white-space: nowrap;
    pointer-events: none;
}

.order-info {
    display: flex;
    gap: 8px;
}

.dragging {
    z-index: 1000;
    opacity: 0.8;
}

.resizing {
    z-index: 1000;
}

.table-photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: inherit;
}

.table-shape:has(.table-photo-overlay) .table-number,
.table-shape:has(.table-photo-overlay) .occupancy-info {
    position: relative;
    z-index: 1;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
    color: white;
    font-weight: bold;
}
</style>
