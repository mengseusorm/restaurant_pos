<template>
    <LoadingComponent :props="loading" />
    <button type="button" @click="reasonModal" data-modal="#reasonModal"
        class="flex items-center justify-center text-white gap-2 px-4 h-[38px] rounded shadow-db-card bg-[#FB4E4E]">
        <i class="lab lab-close"></i>
        <span class="text-sm capitalize text-white">{{ $t("button.reject") }}</span>
    </button>

    <div id="reasonModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("label.reason") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click.prevent="resetModal"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="rejectOrder">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label for="reason" class="db-field-title">
                                {{ $t("label.reason") }} <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                v-model="form.reason" 
                                v-bind:class="error ? 'invalid' : ''" 
                                id="reason"
                                rows="3"
                                maxlength="255"
                                :placeholder="$t('placeholder.enter_rejection_reason')"
                                class="db-field-control" 
                            ></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <small class="db-field-alert" v-if="error">
                                    {{ error }}
                                </small>
                                <small class="text-gray-500 text-xs">
                                    {{ form.reason.length }}/255
                                </small>
                            </div>
                        </div>
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click.prevent="resetModal">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>

                                <button 
                                    type="submit" 
                                    :disabled="!isFormValid || loading.isActive"
                                    :class="[
                                        'db-btn py-2 text-white',
                                        isFormValid && !loading.isActive 
                                            ? 'bg-red-500 hover:bg-red-600' 
                                            : 'bg-gray-400 cursor-not-allowed'
                                    ]"
                                >
                                    <i v-if="loading.isActive" class="fas fa-spinner fa-spin"></i>
                                    <i v-else class="lab lab-close"></i>
                                    <span>
                                        {{ loading.isActive ? $t("label.processing") : $t("button.reject_order") }}
                                    </span>
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
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import LoadingComponent from "../components/LoadingComponent";

export default {
    name: "TelegramMiniAppOrderReasonComponent",
    components: {
        LoadingComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                reason: "",
            },
            error: "",
        };
    },
    computed: {
        isFormValid() {
            return this.form.reason && this.form.reason.trim().length > 0 && this.form.reason.length <= 255;
        }
    },
    methods: {
        reasonModal: function () {
            appService.modalShow("#reasonModal");
        },
        resetModal: function () {
            appService.modalHide("#reasonModal");
            this.form.reason = "";
            this.error = "";
        },
        rejectOrder: function () {
            // Validate reason input
            if (!this.form.reason || this.form.reason.trim() === '') {
                this.error = this.$t("validation.reason_required");
                return;
            }

            if (this.form.reason.length > 255) {
                this.error = this.$t("validation.reason_too_long");
                return;
            }

            this.error = ""; // Clear any previous errors

            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("adminTelegramMiniAppOrder/rejectOrder", {
                        id: this.$route.params.id,
                        reason: this.form.reason.trim(),
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        appService.modalHide();
                        this.form = {
                            reason: "",
                        };
                        this.error = "";
                        alertService.successFlip(1, this.$t("message.order_rejected_successfully"));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        if (err.response && err.response.data && err.response.data.message) {
                            this.error = err.response.data.message;
                        } else {
                            this.error = this.$t("message.something_went_wrong");
                        }
                    });
            } catch (err) {
                this.loading.isActive = false;
                if (err.response && err.response.data && err.response.data.message) {
                    alertService.error(err.response.data.message);
                } else {
                    alertService.error(this.$t("message.something_went_wrong"));
                }
            }
        },
    },
};
</script>