<template>
    <div class="backdrop"></div>
    <header class="db-header bg-white border-b border-gray-200">
        <!-- Logo -->
        <!-- <router-link :to="{ name: 'admin.dashboard' }">
            <i class="lab lab-shop lab-font-size-16 text-blue-500"></i>
        </router-link> -->
        <!-- <button class="fa-solid fa-align-left db-header-nav w-9 h-9 rounded-lg text-primary bg-primary/5"></button> -->
        <router-link class="w-24 flex-shrink-0" :to="{ name: 'admin.dashboard' }">
            <img class="w-full" :src="setting.theme_logo" alt="logo">
        </router-link>

        <!-- Right side controls -->
        <div class="flex items-center justify-end w-full gap-3">
            <!-- Search Toggle Button -->
            <button @click="toggleSearch" type="button" 
                class="w-9 h-9 rounded-lg flex items-center justify-center transition"
                :class="isSearchActive ? 'bg-primary text-white' : 'bg-gray-100 hover:bg-gray-200 text-heading'">
                <i class="lab lab-search-normal lab-font-size-16"></i>
            </button>

            <!-- Fullscreen Toggle -->
            <button @click="fullPage" type="button" 
                class="hidden md:flex w-9 h-9 rounded-lg items-center justify-center bg-gray-100 hover:bg-gray-200 transition">
                <Fullscreen :size="18" />
            </button>

            <!-- POS Orders -->
            <router-link :to="{ name: 'admin.pos.orders.list' }"
                class="px-3 h-9 rounded-lg flex items-center gap-2 bg-primary/10 text-primary hover:bg-primary/20 transition">
                <i class="lab lab-pos-orders lab-font-size-16"></i>
                <span class="hidden md:flex text-sm font-medium">{{ $t('label.orders') }}</span>
            </router-link>

            <!-- User Dropdown -->
            <div class="dropdown-group relative">
                <button type="button" class="dropdown-btn px-3 h-9 rounded-lg flex items-center gap-2 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="lab lab-user lab-font-size-16 text-heading"></i>
                    <span class="capitalize text-sm font-medium text-heading">{{ authInfo.name }}</span>
                    <i class="lab lab-arrow-down text-xs text-heading"></i>
                </button>
                <ul class="dropdown-list p-2 min-w-[180px] rounded-lg shadow-xl absolute top-12 ltr:right-0 rtl:left-0 z-20 border border-gray-200 bg-white hidden">
                    <li>
                        <router-link :to="{ name: 'admin.dashboard' }" 
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm text-heading hover:bg-gray-100 transition">
                            <i class="lab lab-home lab-font-size-16"></i>
                            <span>{{ $t('label.dashboard') }}</span>
                        </router-link>
                    </li>

                    <li>
                        <router-link :to="{ name: 'admin.pos' }" 
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm text-heading hover:bg-gray-100 transition">
                            <i class="lab lab-bag-2 lab-font-size-16"></i>
                            <span>{{ $t('label.full_pos') }}</span>
                        </router-link>
                    </li>
                    
                    <!-- Language Selector -->
                    <li v-if="languages && languages.length > 0" 
                        class="border-t border-gray-200 mt-2 pt-2">
                        <div class="px-3 py-1.5 text-xs font-medium text-gray-500 uppercase">
                            {{ $t('label.language') }}
                        </div>
                        <div v-for="lang in languages" :key="lang.id"
                            @click="changeLanguage(lang.id, lang.code)"
                            class="flex items-center gap-2 px-3 py-2 rounded-md cursor-pointer text-sm hover:bg-gray-100 transition"
                            :class="{ 'bg-primary/10 text-primary': isCurrentLanguage(lang.id) }">
                            <img :src="lang.image" alt="flag" class="w-4 h-4 rounded-full">
                            <span class="capitalize">{{ lang.name }}</span>
                            <i v-if="isCurrentLanguage(lang.id)" class="lab lab-check text-xs ml-auto"></i>
                        </div>
                    </li>
                    
                    <li class="border-t border-gray-200 mt-2 pt-2">
                        <button type="button" @click="logout" 
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm text-red-600 hover:bg-red-50 transition">
                            <i class="lab lab-logout lab-font-size-16"></i>
                            <span>{{ $t('button.logout') }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Internet Connection Alert Modal -->
    <div id="internetAlert" v-if="!internetStatus.isOnline || internetStatus.showAlert" 
        class="modal active ff-modal" style="z-index: 9999;">
        <div class="modal-dialog max-w-[400px] p-6 text-center relative bg-red-50 border-2 border-red-200">
            <div class="mb-4">
                <i class="lab lab-wifi-off text-red-600" style="font-size: 48px;"></i>
            </div>
            <h3 class="text-[20px] font-bold leading-8 mb-4 text-red-700">
                {{ $t('label.no_internet_connection') }}
            </h3>
            <p class="text-red-600 mb-6">
                {{ $t('message.check_internet_connection') }}
            </p>
            <div class="flex flex-col gap-3">
                <button @click="checkInternetConnection" 
                    :disabled="loading.internetCheck"
                    class="w-full px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                    <i v-if="loading.internetCheck" class="lab lab-loading animate-spin mr-2"></i>
                    {{ loading.internetCheck ? $t('label.checking') : $t('button.retry') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import appService from "../../../services/appService";
import internetConnectivityService from "../../../services/internetConnectivityService";
import Fullscreen from 'vue-material-design-icons/Fullscreen.vue';
import activityEnum from "../../../enums/modules/activityEnum";

export default {
    name: "QuickPageNavbarComponent",
    components: {
        Fullscreen,
    },
    data() {
        return {
            activityEnum: activityEnum,
            isSearchActive: false,
            loading: {
                internetCheck: false,
            },
            internetStatus: {
                isOnline: internetConnectivityService.getStatus().isOnline,
                showAlert: false,
                lastCheckTime: internetConnectivityService.getStatus().lastCheckTime
            },
            languageProps: {
                paginate: 0,
                order_column: "id",
                order_type: "asc",
            },
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
        languages: function () {
            return this.$store.getters['frontendLanguage/lists'];
        },
        language: function () {
            const globalState = this.$store.getters['globalState/get'];
            if (globalState && globalState.language_id) {
                const currentLanguage = this.languages.find(lang => lang.id === globalState.language_id);
                if (currentLanguage) {
                    return currentLanguage;
                }
            }
            // Fallback to the first language or show store data
            return this.languages.length > 0 ? this.languages[0] : this.$store.getters['frontendLanguage/show'];
        },
    },
    mounted() {
        appService.responsiveLoad();
        this.initInternetMonitoring();
        
        // Optimize: Load settings and languages in parallel (both use cache)
        Promise.all([
            this.loadSettings(),
            this.loadLanguages()
        ]).catch(err => {
            console.error('Error loading navbar data:', err);
        });
        
        // Listen for search state changes from QuickPosComponent
        window.addEventListener('quickpos-search-state-changed', this.handleSearchStateChange);
    },
    methods: {
        toggleSearch: function() {
            this.isSearchActive = !this.isSearchActive;
            // Emit custom event to toggle search in QuickPosComponent
            window.dispatchEvent(new CustomEvent('toggle-quickpos-search', { 
                detail: { isActive: this.isSearchActive } 
            }));
        },
        handleSearchStateChange: function(event) {
            this.isSearchActive = event.detail.isActive;
        },
        logout: function () {
            this.$store.dispatch("logout").then(res => {
                this.$router.push({ name: "auth.login" });
            }).catch();
        },
        changeLanguage: function (id, code) {
            // Update the i18n locale immediately for UI feedback
            this.$i18n.locale = code;

            // Update global state with the new language
            this.$store.dispatch("globalState/set", { language_id: id, language_code: code }).then(res => {
                // Load the language data
                this.$store.dispatch('frontendLanguage/show', id).then(res => {
                    console.log('Language changed to:', code);
                }).catch(err => {
                    console.error('Error loading language:', err);
                });
            }).catch(err => {
                console.error('Error changing language:', err);
            });
        },
        isCurrentLanguage: function (id) {
            return this.language?.id === id;
        },
        loadLanguages: function() {
            // Return a promise for parallel loading
            return this.$store.dispatch('frontendLanguage/lists', this.languageProps).then(res => {
                console.log('Languages loaded:', this.languages);
                return res;
            }).catch((err) => {
                console.error('Error loading languages:', err);
                throw err;
            });
        },
        fullPage: function() {
            if (!document.fullscreenElement &&
                !document.mozFullScreenElement &&
                !document.webkitFullscreenElement &&
                !document.msFullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        },
        clearCache: function() {
            appService.clearCacheAlert().then((res) => {
                // Cache cleared
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },
        loadSettings: function() {
            // Return a promise for parallel loading
            if (this.setting && Object.keys(this.setting).length > 0) {
                return Promise.resolve(); // Already loaded
            }

            return this.$store.dispatch('frontendSetting/lists').then(res => {
                // Settings loaded (uses cache)
                return res;
            }).catch((err) => {
                console.error('Error loading settings:', err);
                throw err;
            });
        },
        initInternetMonitoring: function() {
            internetConnectivityService.addListener(this.handleConnectivityChange);

            const status = internetConnectivityService.getStatus();
            this.internetStatus.isOnline = status.isOnline;
            this.internetStatus.lastCheckTime = status.lastCheckTime;
            this.internetStatus.showAlert = !status.isOnline;
        },
        handleConnectivityChange: function(isOnline) {
            this.internetStatus.isOnline = isOnline;
            this.internetStatus.showAlert = !isOnline;
            this.internetStatus.lastCheckTime = internetConnectivityService.getStatus().lastCheckTime;
        },
        checkInternetConnection: function() {
            this.loading.internetCheck = true;

            internetConnectivityService.forceCheck()
                .then(() => {
                    this.loading.internetCheck = false;
                    const status = internetConnectivityService.getStatus();
                    this.internetStatus.isOnline = status.isOnline;
                    this.internetStatus.showAlert = !status.isOnline;
                })
                .catch(() => {
                    this.loading.internetCheck = false;
                });
        },
    },
    beforeUnmount() {
        internetConnectivityService.removeListener(this.handleConnectivityChange);
        window.removeEventListener('quickpos-search-state-changed', this.handleSearchStateChange);
    }
}
</script>

<style scoped>
.db-header {
    @apply fixed top-0 left-0 w-full h-[70px] px-5 flex items-center justify-between gap-4 z-40;
}

.dropdown-group {
    @apply relative;
}

.dropdown-list {
    @apply absolute top-10 ltr:right-0 rtl:left-0 w-44 p-2 rounded-lg shadow-xl bg-white border border-gray-200 z-20 hidden;
}

.dropdown-group:hover .dropdown-list,
.dropdown-group:focus-within .dropdown-list {
    @apply block;
}
.db-sidebar{
    z-index:39!important;
    top: 4rem;
}
</style>
