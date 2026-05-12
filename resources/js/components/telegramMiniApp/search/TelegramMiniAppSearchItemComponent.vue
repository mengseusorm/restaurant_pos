<template>
    <TelegramMiniAppLoadingComponent :props="loading" />
    <section class="mb-16 bg-gray-50">
        <div class="container px-4 py-4">
            <!-- Header with Back Button, Search Name, and View Toggle -->
            <div class="flex items-center justify-between gap-3 mb-6">
                <!-- Back Button + Search Name -->
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <button 
                        @click="goBack" 
                        class="flex-shrink-0 w-9 h-9 rounded-full bg-white flex items-center justify-center hover:bg-gray-100 transition-colors shadow-sm">
                        <i class="fa-solid fa-arrow-left text-gray-700 text-sm"></i>
                    </button>
                    <h2 class="text-base font-semibold text-gray-900 truncate">
                        {{ props.search.name }}
                    </h2>
                </div>
                
                <!-- View Toggle Buttons -->
                <div class="flex items-center gap-2 bg-white rounded-full p-1 shadow-sm flex-shrink-0" v-if="props.search.name">
                    <button 
                        type="button" 
                        @click="itemProps.design = itemDesignEnum.LIST" 
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center transition-all',
                            itemProps.design === itemDesignEnum.LIST 
                                ? 'bg-primary text-white shadow' 
                                : 'text-gray-400 hover:text-gray-600'
                        ]">
                        <i class="lab lab-row-vertical text-lg"></i>
                    </button>
                    <button 
                        type="button" 
                        @click="itemProps.design = itemDesignEnum.GRID" 
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center transition-all',
                            itemProps.design === itemDesignEnum.GRID 
                                ? 'bg-primary text-white shadow' 
                                : 'text-gray-400 hover:text-gray-600'
                        ]">
                        <i class="lab lab-element-3 text-lg"></i>
                    </button>
                </div>
            </div>
            
            <TelegramMiniAppItemComponent :items="items" :type="itemProps.type" :design="itemProps.design" />
        </div>
    </section>
</template>

<script>
import TelegramMiniAppItemComponent from "../components/TelegramMiniAppItemComponent";
import itemDesignEnum from "../../../enums/modules/itemDesignEnum";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import TelegramMiniAppLoadingComponent from "../components/TelegramMiniAppLoadingComponent";

export default {
    name: "TelegramMiniAppSearchItemComponent",
    components: {
        TelegramMiniAppItemComponent,
        TelegramMiniAppLoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            itemDesignEnum: itemDesignEnum,
            items: {},
            itemProps: {
                design: itemDesignEnum.GRID,
                type: null,
            },
            props: {
                search: {
                    paginate: 0,
                    order_column: 'id',
                    order_type: 'asc',
                    name: "",
                    status: statusEnum.ACTIVE,
                }
            },
        };
    },
    mounted() {
        if (typeof this.$route.query.s !== "undefined" && this.$route.query.s !== "") {
            this.props.search.name = this.$route.query.s;
            this.loading.isActive = true;
            this.$store.dispatch("frontendItem/lists", this.props.search).then((res) => {
                this.items = res.data.data;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        }
    },
    methods: {
        goBack: function () {
            // Navigate back to menu page
            this.$router.push({ 
                name: 'telegram.mini.app.menu', 
                params: { slug: this.$route.params.slug } 
            });
        },
        searItems: function () {
            if (typeof this.$route.query.s !== "undefined" && this.$route.query.s !== "") {
                this.props.search.name = this.$route.query.s;
                this.loading.isActive = true;
                this.$store.dispatch("frontendItem/lists", this.props.search).then((res) => {
                    this.items = res.data.data;
                    this.loading.isActive = false;
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
            }
        }

    },
    watch: {
        $route() {
            this.searItems();
        }
    }
};
</script>
