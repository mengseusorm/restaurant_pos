<template>
    <LoadingComponent :props="loading" />
    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t('menu.shop_categories') }}</h3>
        </div>
        <div class="db-card-body">
            <div class="row">
                <div class="col-12">
                    <h3 class="text-lg font-medium capitalize mb-2 text-paragraph">{{ shopCategory.name }}</h3>
                    <div class="mb-3">
                        <label class="db-field-title">{{ $t('label.sort') }}</label>
                        <p class="text-gray-600">{{ shopCategory.sort }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="db-field-title">{{ $t('label.status') }}</label>
                        <span class="db-badge" :class="statusClass(shopCategory.status)">
                            {{ enums.statusEnumArray[shopCategory.status] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";

export default {
    name: "ShopCategoryShowComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            }
        }
    },
    computed: {
        shopCategory: function () {
            return this.$store.getters['shopCategory/show'];
        }
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch('shopCategory/show', this.$route.params.id).then(res => {
            this.loading.isActive = false;
        }).catch((error) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        }
    }
}
</script>
