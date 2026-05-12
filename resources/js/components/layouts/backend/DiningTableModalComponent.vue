<template>
    <div id="diningtabletopbarModal" class="modal">
        <div class="modal-dialog max-w-[840px]">
            <div class="modal-header hidden-print">
                <h3 class="drawer-title">{{ $t("menu.select_table") }}</h3>
                <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="swiper size-swiper">
                        <div class="size-tabs">
                            <Swiper :speed="1000" slidesPerView="auto" :spaceBetween="16">
                                <SwiperSlide class="!w-fit" v-for="(item, index) in diningtables" :key="index">
                                    <label :class="[
                                            'variation-margin-right w-full h-[52px] cursor-pointer py-2 px-3 gap-2 rounded-lg flex items-center border transition',
                                            { 
                                                'border-primary': item.current_order_id,
                                                'border-gray-200': !item.current_order_id
                                            }
                                        ]">
                                        <div class="custom-radio sm">
                                            <input type="radio" class="custom-radio-field"
                                                :checked="item.current_order_id != null" @click="selectDiningTable()" />
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <div>
                                            <h3 class="block capitalize text-xs text-heading">
                                                {{ item.name }}
                                            </h3>
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            {{ item.size }} seats
                                        </span>
                                        <button class="db-btn-outline sm primary modal-btn m-0.5"
                                            @click="showOrderDiningTable(item.current_order_id)"
                                            v-if="item.current_order_id">
                                            <i class="lab lab-view"></i>
                                        </button>
                                    </label>
                                </SwiperSlide>
                            </Swiper>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</template>

<script>
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import LoadingComponent from "../../admin/components/LoadingComponent.vue";
import 'swiper/css';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { routes } from "vue-router/auto-routes";

export default {
    name: "DiningTableModalComponent",
    components: {
        LoadingComponent,
        SwiperSlide,
        Swiper
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            props: {
                form: {
                    name: "",
                    email: "",
                    phone: "",
                    password: "",
                    password_confirmation: "",
                    country_code: "",
                    status: statusEnum.ACTIVE,
                }
            },
            errors: {},
            flag: "",
            country_code: "",
        }
    },
    computed: {
        diningtables: function () {
            return this.$store.getters["diningTable/lists"];
        },
    },
    mounted() {
        this.$store.dispatch("diningTable/lists", {
            order_column: "id",
            order_type: "asc",
        });
    },
    methods: {  
        selectDiningTable: function () {
            // alert('hello')
        },
        showOrderDiningTable: function (orderId) {
            if (orderId) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
                this.reset();
            } 
        },
        reset: function () {
            appService.modalHide();
            this.errors = {};
            this.props.form = {
                name: "",
                email: "",
                phone: "",
                password: "",
                password_confirmation: "",
                status: statusEnum.ACTIVE,
                country_code: this.country_code,
            };
        },

        save: function () {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("posOrder/saveCustomer", this.props)
                    .then((res) => {
                        appService.sideDrawerHide();
                        this.loading.isActive = false;
                        alertService.successFlip(0,
                            this.$t("menu.customers")
                        );
                        this.props.form = {
                            name: "",
                            email: "",
                            phone: "",
                            password: "",
                            password_confirmation: "",
                            status: statusEnum.ACTIVE,
                            country_code: this.country_code,
                        };
                        this.errors = {};
                        this.$emit('onCustomverCreate', res.data.data.id);
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.errors = err.response.data.errors;
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
    },
}
</script>

