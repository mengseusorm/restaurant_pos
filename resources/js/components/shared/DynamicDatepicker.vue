<template>
    <RealDatepicker v-bind="datepickerAttrs">
        <template v-for="(_, slot) in $slots" #[slot]="scope">
            <slot :name="slot" v-bind="scope || {}" />
        </template>
    </RealDatepicker>
</template>

<script>
import RealDatepicker from "../../../../node_modules/@vuepic/vue-datepicker/dist/vue-datepicker.js";
import store from "../../store";
import appService from "../../services/appService";

export default {
    name: "DynamicDatepicker",
    components: { RealDatepicker },
    inheritAttrs: false,
    computed: {
        setting() {
            return store.getters['frontendSetting/lists'] || {};
        },
        datepickerAttrs() {
            const attrs = { ...this.$attrs };
            const dateFormat = this.setting.site_date_format || 'd/m/Y';
            const timeFormat = this.setting.site_time_format || 'h:i A';

            if (typeof attrs.format === 'undefined' && typeof attrs.formats === 'undefined') {
                attrs.format = attrs.enableTimePicker === false
                    ? appService.phpDateToDatepickerFormat(dateFormat)
                    : appService.datepickerDateTimeFormat(dateFormat, timeFormat);
            }

            if (typeof attrs.is24 === 'undefined') {
                attrs.is24 = appService.is24HourTimeFormat(timeFormat);
            }

            return attrs;
        },
    },
};
</script>
