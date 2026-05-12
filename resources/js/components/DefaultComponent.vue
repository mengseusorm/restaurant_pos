<template>
    <div :dir="direction">
        <div v-if="theme === 'frontend'">
            <router-view></router-view>
        </div>

        <div v-if="theme === 'backend'">
            <main class="db-main" v-if="logged">
                <BackendNavbarComponent />
                <BackendMenuComponent :key="showMenuType" /> 
                <router-view></router-view> 
            </main> 
            <div v-if="!logged">
                <router-view></router-view>  
            </div>
        </div>

        <div v-if="theme === 'table'">
            <TableNavbarComponent />
            <TableCartComponent />
            <router-view></router-view>
            <TableFooterComponent />
        </div>

        <div v-if="theme === 'onlineOrder'">
            <OnlineOrderNavbarComponent />
            <OnlineOrderCartComponent />
            <router-view></router-view>
            <OnlineOrderFooterComponent />
        </div>

        <div v-if="theme === 'telegramMiniApp'">
            <TelegramMiniAppNavbarComponent />
            <TelegramMiniAppCartComponent />
            <router-view></router-view>
            <TelegramMiniAppFooterComponent />
        </div>

        <div v-if="theme === 'blank'">
            <router-view></router-view>
        </div>

        <div v-if="theme === 'quickPage'">
            <QuickPageNavbarComponent />
            <!-- <BackendMenuComponent />  -->
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import BackendNavbarComponent from "./layouts/backend/BackendNavbarComponent";
import BackendMenuComponent from "./layouts/backend/BackendMenuComponent"; 

import TableNavbarComponent from "./layouts/table/TableNavBarComponent.vue";
import TableFooterComponent from "./layouts/table/TableFooterComponent.vue";
import TableCartComponent from "./layouts/table/TableCartComponent.vue";

import OnlineOrderNavbarComponent from "./layouts/onlineOrder/OnlineOrderNavBarComponent.vue";
import OnlineOrderFooterComponent from "./layouts/onlineOrder/OnlineOrderFooterComponent.vue";
import OnlineOrderCartComponent from "./layouts/onlineOrder/OnlineOrderCartComponent.vue";

import TelegramMiniAppNavbarComponent from "./layouts/telegramMiniApp/TelegramMiniAppNavBarComponent.vue";
import TelegramMiniAppFooterComponent from "./layouts/telegramMiniApp/TelegramMiniAppFooterComponent.vue";
import TelegramMiniAppCartComponent from "./layouts/telegramMiniApp/TelegramMiniAppCartComponent.vue";

import QuickPageNavbarComponent from "./layouts/quickpage/QuickPageNavbarComponent.vue";

import displayModeEnum from "../enums/modules/displayModeEnum";
import env from "../config/env";
import menuTypeEnum from "../enums/modules/menuTypeEnum";

export default {
    name: "DefaultComponent",
    components: {

        TableCartComponent,
        TableFooterComponent,
        TableNavbarComponent,
        
        OnlineOrderCartComponent,
        OnlineOrderFooterComponent,
        OnlineOrderNavbarComponent,

        TelegramMiniAppCartComponent,
        TelegramMiniAppFooterComponent,
        TelegramMiniAppNavbarComponent,

        BackendNavbarComponent, 
        BackendMenuComponent,
        
        QuickPageNavbarComponent,
    },
    data() {
        return {
            theme: "frontend",
        }
    },
    computed: {
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        logged: function () {
            return this.$store.getters.authStatus;
        },
        showMenuType: function () {
            return this.$store.getters['backendGlobalState/showMenuType'];
        }
    },
    beforeMount() {
        this.$store.dispatch('frontendSetting/lists').then(res => {
            this.$store.dispatch('frontendLanguage/show', res.data.data.site_default_language).then(res => {

            }).catch();

            this.$store.dispatch("globalState/init", {
                branch_id: res.data.data.site_default_branch,
                language_id: res.data.data.site_default_language
            });
        }).catch();

        if (env.DEMO === "true" || env.DEMO === true || env.DEMO === "1" || env.DEMO === 1) {
            this.$store.dispatch("authcheck").then(res => {
                if (res.data.status === false && (this.theme == "frontend" || this.theme == "backend")) {
                    this.$router.push({ name: "auth.login" });
                };
            }).catch();
        }
    },
    watch: {
        $route(e) {
            if (e.meta.isFrontend === true) {
                this.theme = "frontend";
            } else if (e.meta.isTable === true) {
                this.theme = "table";
            } else if (e.meta.isOnlineOrder === true) {
                this.theme = "onlineOrder";
            } else if (e.meta.isTelegramMiniApp === true) {
                this.theme = "telegramMiniApp";
            } else if (e.meta.isQuickPage === true) {
                this.theme = "quickPage";
            }else if (e.meta.layout === "blank") {
                this.theme = "blank";
            } else {
                this.theme = "backend";
            }
        }
    }
}
</script>