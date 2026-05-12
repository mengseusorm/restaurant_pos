<template>
    <aside class="db-sidebar">
        <div class="db-sidebar-header"> 
            <button class="fa-solid fa-xmark xmark-btn close-db-menu"></button>
        </div> 
        <nav class="db-sidebar-nav">
            <ul class="db-sidebar-nav-list" v-if="filteredMenus.length > 0">
                <li
                    class="db-sidebar-nav-item"
                    v-for="menu in filteredMenus"
                    :key="menu.id || menu.language"
                >
                    <template v-if="menu.url === '#'">
                        <a href="javascript:void(0);" class="db-sidebar-nav-title">
                            {{ $t('menu.' + menu.language) }}
                        </a>
                    </template>
                    <template v-else>
                        <router-link :to="'/admin/' + menu.url" class="db-sidebar-nav-menu">
                            <i class="text-sm" :class="menu.icon"></i>
                            <span class="text-base flex-auto">{{ $t('menu.' + menu.language) }}</span>
                        </router-link>
                    </template>
                    <ul v-if="menu.children && menu.children.length > 0" class="db-sidebar-nav-list">
                        <li
                            class="db-sidebar-nav-item"
                            v-for="child in menu.children"
                            :key="child.id || child.language"
                        >
                            <router-link :to="'/admin/' + child.url" class="db-sidebar-nav-menu">
                                <i class="text-sm" :class="child.icon"></i>
                                <span class="text-base flex-auto">{{ $t('menu.' + child.language) }}</span>
                            </router-link>
                        </li>
                    </ul>
                </li>
            </ul> 
        </nav>   
    </aside>
</template>
<script>
import menuTypeEnum from '../../../enums/modules/menuTypeEnum';

export default {
    name: "BackendMenuComponent",
    props: {
        type: {
            type: Number,
            default: menuTypeEnum.POS
        }
    },
    data: function () {
        return {
            activeParentId: 1,
            activeChildId: 0,
            menuTypeEnum: menuTypeEnum
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        menus: function () {   
            return this.$store.getters.authMenu;
        },
        showMenuType: function () {
            return this.$store.getters['backendGlobalState/showMenuType'];
        },
        filteredMenus: function () {
            if (!this.menus || this.menus.length === 0) {
                return [];
            }
            // Use showMenuType from store, fallback to prop if not available
            const menuType = this.showMenuType || this.type;
            return this.menus.filter(menu => menu.type === menuType);
        }
    }
}
</script>