<template>
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('menu.branch_daily_sale_report') }}</h3>
            </div>
            <div class="db-card-body">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "BranchDailySaleReportComponent"
}
</script>
