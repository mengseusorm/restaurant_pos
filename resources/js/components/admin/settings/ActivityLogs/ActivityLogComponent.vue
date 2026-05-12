<template>
    <div class="col-12">
        <div class="row">
            <div class="col-12">
                <div class="db-breadcrumb">
                    <h3 class="db-breadcrumb-title">{{ $t('menu.activity_logs') }}</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <router-link :to="{ name: 'admin.dashboard' }" class="router-link">{{ $t('menu.dashboard') }}</router-link>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $t('menu.activity_logs') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <router-view />
        </div>
    </div>
</template>

<script>
export default {
    name: "ActivityLogComponent"
}
</script>
