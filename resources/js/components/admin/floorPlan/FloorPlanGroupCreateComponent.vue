<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="groupModal" class="modal">
        <div class="modal-dialog modal-dialog max-w-3xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("label.add_floor_plan_group") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="groupName" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="groupName" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="sortOrder" class="db-field-title">{{ $t("label.sort_order") }}</label>
                            <input v-model="props.form.sort_order" v-bind:class="errors.sort_order ? 'invalid' : ''" type="number"
                                id="sortOrder" class="db-field-control" min="0" />
                            <small class="db-field-alert" v-if="errors.sort_order">{{ errors.sort_order[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-12">
                            <label for="branch_id" class="db-field-title required">{{ $t("label.branch") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="branch_id"
                                v-bind:class="errors.branch_id ? 'invalid' : ''"
                                v-model="props.form.branch_id" :options="branches" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" :default-selected="1"/>
                            <small class="db-field-alert" v-if="errors.branch_id">{{ errors.branch_id[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="groupDescription" class="db-field-title">{{ $t("label.description") }}</label>
                            <textarea v-model="props.form.description" v-bind:class="errors.description ? 'invalid' : ''"
                                id="groupDescription" class="db-field-control" rows="3"></textarea>
                            <small class="db-field-alert" v-if="errors.description">{{ errors.description[0] }}</small>
                        </div>
                        <!-- Floor Plan Photo Upload -->
                        <div class="form-col-12">
                            <label for="floorPlanPhoto" class="db-field-title">{{ $t("label.floor_plan_photo") }}</label>
                            <div class="photo-upload-container">
                                <!-- Photo Preview -->
                                <div v-if="photoPreview" class="photo-preview mb-4">
                                    <img :src="photoPreview" alt="Floor Plan Preview" class="preview-image" />
                                    <button type="button" @click="removePhoto" class="remove-photo-btn">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>

                                <!-- Upload Area -->
                                <div v-if="!photoPreview" class="upload-area" @click="$refs.photoInput.click()">
                                    <i class="fa-solid fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-600">{{ $t("label.click_to_upload_photo") }}</p>
                                    <p class="text-sm text-gray-400">{{ $t("label.max_file_size_5mb") }}</p>
                                </div>

                                <input
                                    ref="photoInput"
                                    type="file"
                                    id="floorPlanPhoto"
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
                                <button type="submit" class="db-btn py-2 text-white bg-primary" @click="save">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.save") }}</span>
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
import SmModalCreateComponent from '../components/buttons/SmModalCreateComponent.vue';
import LoadingComponent from '../components/LoadingComponent.vue';
import axios from 'axios';

export default {
    name: "FloorPlanGroupCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ["props"],
    emits: ['groupCreated'],
    data() {
        return {
            loading: {
                isActive: false,
            },
            errors: {},
            photo: null,
            photoPreview: null,
            uploadProgress: 0,
            uploading: false,
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('label.add_group') };
        },
        branches: function () {
            return this.$store.getters["branch/lists"];
        },
        authBranch: function () {
            return this.$store.getters.authBranchId;
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
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("floorPlan/reset").then().catch();
            this.errors = {};
            this.photo = null;
            this.photoPreview = null;
            this.uploadProgress = 0;
            this.uploading = false;
            this.$props.props.form = {
                branch_id: this.$store.getters['defaultAccess/show'].branch_id,
                name: "",
                description: "",
                sort_order: 0,
            };
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

        save: function () {
            try {
                const data = {
                    name: this.props.form.name,
                    description: this.props.form.description,
                    sort_order: this.props.form.sort_order,
                    branch_id: this.props.form.branch_id,
                };

                const tempId = this.$store.getters["floorPlan/temp"].temp_id;
                this.loading.isActive = true;

                this.$store.dispatch("floorPlan/createGroup", data).then((res) => {
                    const groupId = res.data.data.id;

                    // Upload photo if selected
                    if (this.photo) {
                        this.uploadPhoto(groupId).then(() => {
                            this.finalizeSave(tempId, res.data.data);
                        }).catch((err) => {
                            console.error('Photo upload failed:', err);
                            // Still finalize save even if photo upload fails
                            this.finalizeSave(tempId, res.data.data);
                        });
                    } else {
                        this.finalizeSave(tempId, res.data.data);
                    }
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },

        uploadPhoto: function (groupId) {
            return new Promise((resolve, reject) => {
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

        finalizeSave: function (tempId, groupData) {
            appService.modalHide();
            this.loading.isActive = false;
            alertService.successFlip(
                tempId === null ? 0 : 1,
                this.$t("label.floor_plan_group")
            );
            this.props.form = {
                branch_id: this.props.form.branch_id,
                name: "",
                description: "",
                sort_order: 0,
            };
            this.errors = {};
            this.photo = null;
            this.photoPreview = null;
            this.uploadProgress = 0;
            this.uploading = false;
            this.$emit('groupCreated', groupData);
        },
    },
};
</script>


