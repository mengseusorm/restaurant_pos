<template>
    <div>
        <label v-if="label" :for="id" :class="labelClass">{{ label }}</label>
        <vue-select 
            v-if="branches.length > 0" 
            :class="selectClass" 
            :id="id"
            :modelValue="modelValue"
            @update:modelValue="updateValue"
            :options="branches" 
            label-by="name" 
            value-by="id" 
            :closeOnSelect="closeOnSelect"
            :searchable="searchable" 
            :clearOnClose="clearOnClose" 
            :placeholder="placeholder" 
            :search-placeholder="searchPlaceholder"
            :disabled="disabled"
            :required="required" />
        <small v-if="error" class="db-field-alert">{{ error }}</small>
    </div>
</template>

<script>
export default {
    name: "BranchSelectComponent",
    props: {
        modelValue: {
            type: [Number, String],
            default: null
        },
        id: {
            type: String,
            default: 'branch_id'
        },
        label: {
            type: String,
            default: null
        },
        labelClass: {
            type: String,
            default: 'db-field-title'
        },
        selectClass: {
            type: String,
            default: 'db-field-control f-b-custom-select'
        },
        error: {
            type: String,
            default: null
        },
        placeholder: {
            type: String,
            default: '--'
        },
        searchPlaceholder: {
            type: String,
            default: '--'
        },
        closeOnSelect: {
            type: Boolean,
            default: true
        },
        searchable: {
            type: Boolean,
            default: true
        },
        clearOnClose: {
            type: Boolean,
            default: true
        },
        disabled: {
            type: Boolean,
            default: false
        },
        required: {
            type: Boolean,
            default: false
        },
        includeAllShop: {
            type: Boolean,
            default: true
        }
    },
    computed: {
        branches() {
            const branchList = this.$store.getters["branch/lists"] || [];
            if (this.includeAllShop) {
                return [
                    { id: 0, name: this.$t('label.all_branches') || 'All Shop' },
                    ...branchList
                ];
            }
            return branchList;
        }
    },
    mounted() {
        // Auto-load branches if not already loaded
        if (!this.$store.getters["branch/lists"] || this.$store.getters["branch/lists"].length === 0) {
            this.$store.dispatch("branch/lists", {
                order_column: "id",
                order_type: "asc",
            });
        }
    },
    methods: {
        updateValue(value) {
            this.$emit('update:modelValue', value);
        }
    }
}
</script>
