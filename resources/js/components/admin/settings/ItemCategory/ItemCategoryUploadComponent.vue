<template>
    <LoadingComponent :props="loading" />
    <div :id="dataModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ modalTitle }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label class="db-field-title required">{{ $t("label.upload_file") }} ({{ fileTypeLabel }})</label>
                            <input @change="changeFile" v-bind:class="errors.file ? 'invalid' : ''"
                                :id="`file-${dataModal}`"
                                type="file" class="db-field-control" ref="fileProperty"
                                :accept="acceptedFileTypes"
                                :multiple="isMultiple" />
                            <small class="db-field-alert" v-if="errors.file">{{ errors.file[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>

                                <button type="submit" class="db-btn py-2 text-white bg-primary">
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

import LoadingComponent from "../../components/LoadingComponent.vue";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";

export default {
    name: "ItemCategoryUploadComponent",
    components: { LoadingComponent },
    emits:['list'],
    props: {
        dataModal: {
            type: String,
            required: true
        },
        uploadType: {
            type: String,
            default: 'items', // 'items' or 'images'
            validator: (value) => ['items', 'images'].includes(value)
        },
        context: {
            type: String,
            default: 'itemCategory'
        }
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            file: "",
            search: {
                paginate: 1,
                page: 1,
                per_page: 10,
                order_column: "id",
                order_type: "desc",
            },
            errors: {},
            files: [],
        };
    },
    computed: {
        modalTitle() {
            return this.uploadType === 'images'
                ? this.$t('button.bulk_upload_image')
                : this.$t('menu.item_categories');
        },
        fileTypeLabel() {
            return this.uploadType === 'images'
                ? this.$t('label.images')
                : this.$t('label.xlsx');
        },
        acceptedFileTypes() {
            return this.uploadType === 'images'
                ? '.jpg, .jpeg, .png, .gif, .webp'
                : '.xlsx, .xls';
        },
        isMultiple() {
            return this.uploadType === 'images';
        },
        uploadAction() {
            return this.uploadType === 'images'
                ? `${this.context}/uploadImages`
                : `${this.context}/import`;
        }
    },
    methods: {
        reset: function () {
            appService.modalHide();
            this.file = "";
            this.files = [];
            this.errors = {};
            this.$refs.fileProperty.value = null;
        },
        changeFile: function (e) {
            if (this.uploadType === 'images') {
                this.files = Array.from(e.target.files);
            } else {
                this.file = e.target.files[0];
            }
        },
        save: function () {
            try {
                const fd = new FormData();

                if (this.uploadType === 'images') {
                    if (this.files.length === 0) {
                        alertService.error(this.$t('message.please_select_file'));
                        return;
                    }
                    this.files.forEach((file) => {
                        fd.append('files[]', file);
                    });
                } else {
                    if (!this.file) {
                        alertService.error(this.$t('message.please_select_file'));
                        return;
                    }
                    fd.append('file', this.file);
                }

                this.loading.isActive = true;
                this.$store.dispatch(this.uploadAction, {
                    form: fd,
                    search: this.search
                }).then((res) => {
                    this.loading.isActive = false;
                    appService.modalHide();
                    const successMessage = this.uploadType === 'images'
                        ? this.$t('button.bulk_upload_image')
                        : this.$t('menu.item_categories');
                    alertService.successFlip(0, successMessage);
                    this.file = "";
                    this.files = [];
                    this.errors = {};
                    this.$refs.fileProperty.value = null;
                    this.$emit('list');
                }).catch((err) => {
                    this.loading.isActive = false;

                    if(err.response.data?.message){
                        alertService.error(err.response.data.message);
                    }else{
                        this.errors = err.response.data.errors;
                    }

                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
    },
};
</script>
