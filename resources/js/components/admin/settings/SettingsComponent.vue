<template>
    <div class="row">
        <div class="col-12">
            <BreadcrumbComponent />
        </div>

        <div class="col-12 md:col-3 xl:col-2">
            <MenuComponent />
        </div>

        <div class="col-12 md:col-9 xl:col-10">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import MenuComponent from "./MenuComponent";
import BreadcrumbComponent from "../components/BreadcrumbComponent";

export default {
    name: "SettingsComponent",
    components: {MenuComponent, BreadcrumbComponent},

}
</script>
