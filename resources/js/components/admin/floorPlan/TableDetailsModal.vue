<template>
    <div id="tableDetailsModal" class="modal">
        <div class="modal-dialog modal-dialog max-w-4xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('label.table_details') }} - {{ table.name }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="closeModal"></button>
            </div>
            <div class="modal-body">
                <!-- Table Basic Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="info-card">
                        <div class="info-label">{{ $t('label.size') }}</div>
                        <div class="info-value">{{ table.size }} {{ $t('label.seats') }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">{{ $t('label.current_guests') }}</div>
                        <div class="info-value">{{ table.current_guests || 0 }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">{{ $t('label.status') }}</div>
                        <div class="info-value">
                            <span :class="table.is_occupied ? 'text-red-600' : 'text-green-600'">
                                {{ table.is_occupied ? $t('label.occupied') : $t('label.available') }}
                            </span>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">{{ $t('label.occupancy_rate') }}</div>
                        <div class="info-value">{{ table.occupancy_rate || 0 }}%</div>
                    </div>
                </div>

                <!-- Current Order Info -->
                <div v-if="table.current_order_id && table.orders" class="order-info mb-6">
                    <h4 class="text-lg font-semibold mb-3">{{ $t('label.current_order') }}</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <span class="text-sm text-gray-600">{{ $t('label.order_number') }}:</span>
                                <span class="ml-2 font-medium">#{{ table.orders.order_serial_no }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">{{ $t('label.total') }}:</span>
                                <span class="ml-2 font-medium">${{ table.orders.total || 0 }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">{{ $t('label.started_at') }}:</span>
                                <span class="ml-2 font-medium">{{ formatDateTime(table.orders.created_at) }}</span>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div v-if="table.orders.order_items && table.orders.order_items.length > 0">
                            <h5 class="font-medium mb-2">{{ $t('label.order_items') }}:</h5>
                            <div class="space-y-2">
                                <div v-for="item in table.orders.order_items" :key="item.id" 
                                     class="flex justify-between items-center bg-white p-2 rounded">
                                    <div>
                                        <span class="font-medium">{{ item.item?.name }}</span>
                                        <span class="text-sm text-gray-600 ml-2">x{{ item.quantity }}</span>
                                    </div>
                                    <span class="font-medium">${{ item.total_price }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Position Info -->
                <div v-if="table.position_x !== null && table.position_y !== null" class="position-info mb-6">
                    <h4 class="text-lg font-semibold mb-3">{{ $t('label.position_info') }}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="info-card">
                            <div class="info-label">X Position</div>
                            <div class="info-value">{{ table.position_x }}px</div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">Y Position</div>
                            <div class="info-value">{{ table.position_y }}px</div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">{{ $t('label.shape') }}</div>
                            <div class="info-value">{{ $t(`label.${table.shape}`) || table.shape }}</div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">{{ $t('label.group') }}</div>
                            <div class="info-value">{{ table.floor_plan_group?.name || $t('label.no_group') }}</div>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div v-if="table.qr" class="qr-section mb-6">
                    <h4 class="text-lg font-semibold mb-3">{{ $t('label.qr_code') }}</h4>
                    <div class="flex items-center justify-center">
                        <img :src="table.qr" :alt="`QR Code for ${table.name}`" class="max-w-32" />
                    </div>
                </div>

                <!-- Table Photo -->
                <div class="photo-section mb-6">
                    <h4 class="text-lg font-semibold mb-3">{{ $t('label.table_photo') }}</h4>
                    <div class="photo-upload-area">
                        <!-- Current Photo -->
                        <div v-if="table.table_photo" class="current-photo mb-4">
                            <img :src="table.table_photo" :alt="`Photo of ${table.name}`" class="max-w-48 h-32 object-cover rounded-lg border" />
                        </div>

                        <!-- Upload Area -->
                        <div v-if="!uploading" class="upload-area">
                            <input ref="photoInput" type="file" accept="image/*" @change="onPhotoSelected" class="hidden">
                            <div @click="openFileDialog" 
                                 @drop="onPhotoDrop" 
                                 @dragover="onPhotoDragOver"
                                 class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-gray-400 transition-colors">
                                <div v-if="uploading" class="flex flex-col items-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-2"></div>
                                    <span class="text-sm text-gray-600">{{ $t('label.uploading') }}... {{ uploadProgress }}%</span>
                                </div>
                                <div v-else class="flex flex-col items-center">
                                    <i class="lab lab-upload text-3xl text-gray-400 mb-2"></i>
                                    <span class="text-sm text-gray-600">{{ $t('label.click_or_drag_photo') }}</span>
                                    <span class="text-xs text-gray-500 mt-1">{{ $t('label.max_file_size_5mb') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Photo Preview -->
                        <div v-if="photoPreview" class="photo-preview mt-4">
                            <img :src="photoPreview" alt="Photo Preview" class="max-w-48 h-32 object-cover rounded-lg border" />
                            <div class="mt-2 space-x-2">
                                <button @click="uploadTablePhoto" class="text-sm text-green-600 hover:text-green-800">
                                    <i class="lab lab-save"></i>
                                    {{ $t('button.upload') }}
                                </button>
                                <button @click="removePhoto" class="text-sm text-red-600 hover:text-red-800">
                                    <i class="lab lab-cancel"></i>
                                    {{ $t('button.cancel') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="form-col-12">
                    <div class="modal-btns">
                        <button type="button" class="modal-btn-outline modal-close" @click="closeModal">
                            <i class="lab lab-close"></i>
                            <span>{{ $t("button.close") }}</span>
                        </button>
                        <button v-if="table.current_order_id" 
                                type="button" 
                                class="db-btn py-2 text-white bg-primary" 
                                @click="viewOrder">
                            <i class="lab lab-eye"></i>
                            <span>{{ $t("button.view_order") }}</span>
                        </button>
                        <button v-if="table.current_order_id" 
                                type="button" 
                                class="db-btn py-2 text-white bg-danger" 
                                @click="releaseTable">
                            <i class="lab lab-unlock"></i>
                            <span>{{ $t("button.release_table") }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import axios from 'axios';

export default {
    name: "TableDetailsModal",
    props: {
        table: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            photo: null,
            photoPreview: null,
            uploadProgress: 0,
            uploading: false,
        };
    },
    methods: {
        formatDateTime(timestamp) {
            if (!timestamp) return '';
            return new Date(timestamp).toLocaleString();
        },
        
        viewOrder() {
            // Emit event to parent to show order details
            this.$emit('viewOrder', this.table);
        },
        
        releaseTable() {
            this.$emit('release', { tableId: this.table.id, orderId: this.table.current_order_id });
        },
        
        closeModal() {
            appService.modalHide();
            this.$emit('close');
        },

        onPhotoSelected: function (event) {
            const file = event.target.files[0];
            this.handleFileSelection(file);
        },

        onPhotoDrop: function (event) {
            event.preventDefault();
            const file = event.dataTransfer.files[0];
            this.handleFileSelection(file);
        },

        onPhotoDragOver: function (event) {
            event.preventDefault();
        },

        handleFileSelection: function (file) {
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alertService.error(this.$t('message.invalid_file_type'));
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alertService.error(this.$t('message.file_too_large'));
                return;
            }

            this.photo = file;
            
            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removePhoto: function () {
            this.photo = null;
            this.photoPreview = null;
            this.$refs.photoInput.value = '';
        },

        openFileDialog: function () {
            this.$refs.photoInput.click();
        },

        uploadTablePhoto: function () {
            if (!this.photo) return;

            const formData = new FormData();
            formData.append('image', this.photo);

            this.uploading = true;
            this.uploadProgress = 0;

            this.$store.dispatch('floorPlan/changeTableImage', {
                id: this.table.id,
                form: formData
            }).then((response) => {
                this.uploading = false;
                this.photo = null;
                this.photoPreview = null;
                this.$refs.photoInput.value = '';
                
                // Update table data
                this.table.table_photo = response.data.data.table_photo;
                
                alertService.success(this.$t('message.photo_uploaded_successfully'));
                this.$emit('photoUploaded', this.table);
            }).catch((error) => {
                this.uploading = false;
                console.error('Photo upload failed:', error);
                alertService.error(this.$t('message.photo_upload_failed'));
            });
        }
    }
}
</script>

<style scoped>
.info-card {
    background: white;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.info-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

.info-value {
    font-weight: 600;
    color: #374151;
}

.order-info {
    border-left: 4px solid #3b82f6;
    padding-left: 16px;
}
</style>
