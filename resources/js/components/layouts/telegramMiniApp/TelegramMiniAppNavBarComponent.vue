<template>
    <LoadingComponent :props="loading" />

    <header class="shadow-xs bg-white/80 backdrop-blur-sm transition-all duration-300 sticky top-0 z-50" ref="ffHeader">
        <div class="container p-4">
            <!-- Main Navigation Row -->
            <div class="flex items-center justify-between">
                <router-link :to="{ name: 'telegram.mini.app.menu', params: { slug: this.$route.params.slug } }" class="flex-shrink-0">
                    <img class="w-24 h-10 sm:w-32 sm:h-10 object-contain" :src="setting.theme_logo" alt="logo" />
                </router-link>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-2">
                    <!-- Language Selector -->
                    <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE" class="relative dropdown-group">
                        <button class="flex items-center justify-center gap-1.5 w-fit rounded-full capitalize text-sm font-medium h-9 px-3 border transition text-heading bg-white border-gray-200 dropdown-btn">
                            <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full" />
                            <span class="whitespace-nowrap text-sm hidden xs:inline">{{ language.name }}</span>
                        </button>
                        <ul v-if="languages.length > 0" class="p-2 min-w-[160px] rounded-lg shadow-xl absolute top-11 right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                            <li @click="changeLanguage(language.id, language.code)" v-for="language in languages" class="flex items-center gap-2 py-1.5 px-2 rounded-md cursor-pointer hover:bg-gray-100">
                                <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full" />
                                <span class="text-heading capitalize text-sm">{{ language.name }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Cart Button -->
                    <button class="telegram-mini-app-webcart flex items-center justify-center gap-1.5 w-fit rounded-full capitalize text-sm font-medium h-9 px-3 transition text-gray-600 bg-white/80 backdrop-blur-sm min-w-0 border border-gray-200">
                        <i class="fa-solid fa-bag-shopping text-sm"></i>
                        <span class="whitespace-nowrap text-sm truncate max-w-20">
                            {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, branch?.currency_id?.symbol || '', setting.site_currency_position) }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import statusEnum from '../../../enums/modules/statusEnum';
import appService from '../../../services/appService';
import LoadingComponent from '../../frontend/components/LoadingComponent';
import activityEnum from '../../../enums/modules/activityEnum';

export default {
    name: 'TelegramMiniAppNavbarComponent',
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            searchItem: '',
            showSearchInput: false,
            enums: {
                activityEnum: activityEnum,
            },
            languageProps: {
                paginate: 0,
                order_column: 'id',
                order_type: 'asc',
                status: statusEnum.ACTIVE,
            },
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'] || {};
        },
        language: function () {
            return this.$store.getters['frontendLanguage/show'] || {};
        },
        languages: function () {
            return this.$store.getters['frontendLanguage/lists'] || [];
        },
        subtotal: function () {
            return this.$store.getters['telegramMiniApp/cart/subtotal'] || 0;
        },
        branch: function () {
            return this.$store.getters['frontendBranch/show'] || {};
        },
    },
    mounted() {
        // Check initial route for auto-search display
        this.checkRouteForAutoSearch();

        window.addEventListener('scroll', () => {
            const resetRoutes = ['telegram.mini.app.order.details', 'telegram.mini.app.page', 'telegram.mini.app.checkout'];
            if (this.$refs.ffHeader) {
                if (!resetRoutes.includes(this.$route.name)) {
                    if (window.scrollY > 0) {
                        this.$refs.ffHeader.classList.add('active');
                    } else {
                        this.$refs.ffHeader.classList.remove('active');
                    }
                } else {
                    this.$refs.ffHeader.classList.remove('active');
                }
            }
        });

        this.loading.isActive = true;
        this.$store
            .dispatch('frontendSetting/lists')
            .then((res) => {
                this.defaultLanguage = res.data.data.site_default_language;
                const globalState = this.$store.getters['globalState/lists'];

                if (globalState.language_id > 0) {
                    this.defaultLanguage = globalState.language_id;
                }

                this.$store.dispatch('frontendLanguage/lists', this.languageProps).then().catch();
                this.$store
                    .dispatch('frontendLanguage/show', this.defaultLanguage)
                    .then((res) => {
                        this.$i18n.locale = res.data.data.code;
                        this.$store.dispatch('globalState/init', {
                            language_code: res.data.data.code,
                        });
                    })
                    .catch();

                window.setTimeout(() => {
                    this.$store
                        .dispatch('telegramMiniApp/branch/show', this.$route.params.slug)
                        .then((res) => {
                            // this.$store.dispatch('telegramMiniAppCart/initTable', res.data.data);

                            this.$store
                                .dispatch('frontendBranch/show', res.data.data.branch_id)
                                .then((res) => {
                                    // location.reload();
                                })
                                .catch();
                        })
                        .catch((err) => {});
                }, 300);

                this.loading.isActive = false;
            })
            .catch((err) => {
                this.loading.isActive = false;
            });
    },
    methods: {
        changeLanguage: function (id, code) {
            this.defaultLanguage = id;
            this.$store
                .dispatch('globalState/set', { language_id: id, language_code: code })
                .then((res) => {
                    this.$store
                        .dispatch('frontendLanguage/show', id)
                        .then((res) => {
                            this.$i18n.locale = res.data.data.code;
                        })
                        .catch();
                })
                .catch();
        },
        currencyFormat(amount, decimal, currency, position) {
            return appService.currencyFormat(amount ? amount : 0, decimal, currency, position);
        },
        search: function () {
            if (typeof this.searchItem !== 'undefined' && this.searchItem !== '') {
                this.$router.push({ name: 'telegram.mini.app.search', query: { s: this.searchItem } });
                this.searchItem = '';
            }
        },
        searchReset: function () {
            this.searchItem = '';
        },
        toggleSearch: function () {
            this.showSearchInput = !this.showSearchInput;
            // Auto-focus the search input when it becomes visible
            if (this.showSearchInput) {
                this.$nextTick(() => {
                    // Focus mobile or desktop input based on screen size
                    const mobileInput = this.$refs.mobileSearchInput;
                    const desktopInput = this.$refs.searchInput;
                    if (mobileInput && window.innerWidth < 1024) {
                        mobileInput.focus();
                    } else if (desktopInput) {
                        desktopInput.focus();
                    }
                });
            } else {
                // Clear search when hiding
                this.searchItem = '';
            }
        },
        checkRouteForAutoSearch: function () {
            // Auto-show search input on menu page
            if (this.$route.name === 'telegram.mini.app.menu') {
                this.showSearchInput = true;
            } else {
                this.showSearchInput = false;
                this.searchItem = '';
            }
        },
        testing() {
            alert('Test');
        },
    },
    watch: {
        $route(to, from) {
            // Check route changes for auto-search display
            this.checkRouteForAutoSearch();
        },
    },
};
</script>
