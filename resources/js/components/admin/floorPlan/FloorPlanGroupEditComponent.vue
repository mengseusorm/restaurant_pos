<template>
    <LoadingComponent :props="loading" />

    <div id="groupEditModal" class="modal">
        <div class="modal-dialog modal-dialog max-w-3xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("label.edit_floor_plan_group") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="editGroupName" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input v-model="form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="editGroupName" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="editSortOrder" class="db-field-title">{{ $t("label.sort_order") }}</label>
                            <input v-model="form.sort_order" v-bind:class="errors.sort_order ? 'invalid' : ''" type="number"
                                id="editSortOrder" class="db-field-control" min="0" />
                            <small class="db-field-alert" v-if="errors.sort_order">{{ errors.sort_order[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-12">
                            <label for="branch_id" class="db-field-title required">{{ $t("label.branch") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="branch_id"
                                v-bind:class="errors.branch_id ? 'invalid' : ''"
                                v-model="form.branch_id" :options="branches" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" :default-selected="1"/>
                            <small class="db-field-alert" v-if="errors.branch_id">{{ errors.branch_id[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="editGroupDescription" class="db-field-title">{{ $t("label.description") }}</label>
                            <textarea v-model="form.description" v-bind:class="errors.description ? 'invalid' : ''"
                                id="editGroupDescription" class="db-field-control" rows="3"></textarea>
                            <small class="db-field-alert" v-if="errors.description">{{ errors.description[0] }}</small>
                        </div>

                        <!-- Floor Plan Photo Upload -->
                        <div class="form-col-12">
                            <label for="editFloorPlanPhoto" class="db-field-title">{{ $t("label.floor_plan_photo") }}</label>
                            <div class="photo-upload-container">
                                <!-- Current Photo Display -->
                                <div v-if="currentPhoto && !photoPreview" class="current-photo mb-4">
                                    <img :src="currentPhoto" alt="Current Floor Plan" class="preview-image" />
                                    <div class="photo-actions mt-2">
                                        <button type="button" @click="changePhoto" class="db-btn py-1 px-3 text-white bg-blue-500 mr-2">
                                            <i class="fa-solid fa-edit"></i>
                                            {{ $t("button.change_photo") }}
                                        </button>
                                        <button type="button" @click="removeCurrentPhoto" class="db-btn py-1 px-3 text-white bg-red-500">
                                            <i class="fa-solid fa-trash"></i>
                                            {{ $t("button.remove_photo") }}
                                        </button>
                                    </div>
                                </div>

                                <!-- New Photo Preview -->
                                <div v-if="photoPreview" class="photo-preview mb-4">
                                    <img :src="photoPreview" alt="New Floor Plan Preview" class="preview-image" />
                                    <button type="button" @click="cancelPhotoChange" class="remove-photo-btn">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>

                                <!-- Upload Area -->
                                <div v-if="!photoPreview && !currentPhoto" class="upload-area" @click="$refs.photoInput.click()">
                                    <i class="fa-solid fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-600">{{ $t("label.click_to_upload_photo") }}</p>
                                    <p class="text-sm text-gray-400">{{ $t("label.max_file_size_5mb") }}</p>
                                </div>

                                <input
                                    ref="photoInput"
                                    type="file"
                                    id="editFloorPlanPhoto"
                                    accept="image/jpeg,image/png,image/jpg,image/gif"
                                    @change="handlePhotoUpload"
                                    class="hidden" />
                            </div>
                            <small class="db-field-alert" v-if="errors.floor_plan_photo">{{ errors.floor_plan_photo[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.update") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import alertService from '../../../services/alertService';
import appService from '../../../services/appService';
import LoadingComponent from '../components/LoadingComponent.vue';

export default {
    name: "FloorPlanGroupEditComponent",
    components: { LoadingComponent },
    props: ["group"],
    emits: ['groupUpdated', 'close'],
    data() {
        return {
            loading: {
                isActive: false,
            },
            errors: {},
            form: {
                name: "",
                description: "",
                sort_order: 0,
                branch_id: null,
            },
            photo: null,
            photoPreview: null,
            currentPhoto: null,
            photoChanged: false,
            uploadProgress: 0,
            uploading: false,
        };
    },
    watch: {
        group: {
            immediate: true,
            handler(newGroup) {
                if (newGroup) {
                    this.populateForm(newGroup);
                }
            }
        }
    },
    computed: {
        branches: function () {
            return this.$store.getters["branch/lists"];
        },
        authBranch: function () {
            return this.$store.getters.authBranchId;
        },
        roles: function () {
            return this.$store.getters["role/lists"];
        },
    },
    mounted(){
        this.$store.dispatch("defaultAccess/show");
        this.$store.dispatch("branch/lists", {
            order_column: "id",
            order_type: "asc",
        });
    },
    methods: {
        populateForm: function(group) {
            this.form = {
                name: group.name || "",
                description: group.description || "",
                sort_order: group.sort_order || 0,
                branch_id: group.branch_id || null,
            };
            this.currentPhoto = group.floor_plan_photo || null;
            this.photoChanged = false;
            this.photo = null;
            this.photoPreview = null;
        },

        reset: function () {
            appService.modalHide();
            this.errors = {};
            this.photo = null;
            this.photoPreview = null;
            this.photoChanged = false;
            this.uploadProgress = 0;
            this.uploading = false;
            this.$emit('close');
        },

        changePhoto: function() {
            this.$refs.photoInput.click();
        },

        removeCurrentPhoto: function() {
            this.currentPhoto = null;
            this.photoChanged = true;
        },

        cancelPhotoChange: function() {
            this.photo = null;
            this.photoPreview = null;
            this.photoChanged = false;
            this.$refs.photoInput.value = '';
        },

        handlePhotoUpload: function(event) {
            const file = event.target.files[0];
            this.handleFileSelection(file);
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
            this.photoChanged = true;

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        save: function () {
            try {
                if (!this.group || !this.group.id) {
                    alertService.error(this.$t('message.no_group_selected'));
                    return;
                }

                const data = {
                    name: this.form.name,
                    description: this.form.description,
                    sort_order: this.form.sort_order,
                    branch_id: this.form.branch_id,
                };

                this.loading.isActive = true;

                this.$store.dispatch("floorPlan/updateGroup", {
                    groupId: this.group.id,
                    form: data
                }).then((res) => {
                    // Upload photo if changed
                    if (this.photoChanged) {
                        if (this.photo) {
                            this.uploadPhoto(this.group.id).then(() => {
                                this.finalizeSave(res.data.data);
                            }).catch((err) => {
                                console.error('Photo upload failed:', err);
                                // Still finalize save even if photo upload fails
                                this.finalizeSave(res.data.data);
                            });
                        } else {
                            // Photo was removed
                            this.finalizeSave(res.data.data);
                        }
                    } else {
                        this.finalizeSave(res.data.data);
                    }
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response?.data?.errors || {};
                    alertService.error(err.response?.data?.message || this.$t('message.update_failed'));
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },

        uploadPhoto: function (groupId) {
            return new Promise((resolve, reject) => {
                if (!this.photo) {
                    resolve();
                    return;
                }

                const formData = new FormData();
                formData.append('image', this.photo);

                this.uploading = true;
                this.uploadProgress = 0;

                this.$store.dispatch('floorPlan/changeGroupImage', {
                    id: groupId,
                    form: formData
                }).then((response) => {
                    this.uploading = false;
                    resolve(response);
                }).catch((error) => {
                    this.uploading = false;
                    reject(error);
                });
            });
        },

        finalizeSave: function (groupData) {
            appService.modalHide();
            this.loading.isActive = false;
            alertService.success(this.$t("message.floor_plan_group_updated_successfully"));

            this.errors = {};
            this.photo = null;
            this.photoPreview = null;
            this.photoChanged = false;
            this.uploadProgress = 0;
            this.uploading = false;

            this.$emit('groupUpdated', groupData);
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
.photo-upload-container {
    border: 2px dashed #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
}

.upload-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-area:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
}

.photo-preview, .current-photo {
    position: relative;
    display: inline-block;
}

.preview-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.remove-photo-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
}

.remove-photo-btn:hover {
    background: #dc2626;
}

.photo-actions {
    display: flex;
    gap: 0.5rem;
}

.progress-bar {
    width: 100%;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: #3b82f6;
    transition: width 0.3s ease;
}
</style>
