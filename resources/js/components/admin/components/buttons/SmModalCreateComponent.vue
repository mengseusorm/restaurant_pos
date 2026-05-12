<template>
    <button 
        type="button" 
        @click="add" 
        :data-modal="props.modalId || '#modal'" 
        class="db-btn h-[37px] text-white bg-primary">
        <i class="lab lab-add-circle-line"></i>
        <span>{{ props.title }}</span>
    </button>
</template>

<script>
import appService from "../../../../services/appService";

export default {
    name: "SmModalCreateComponent",
    props: ['props'],
    methods: {
        add: function () {
            if(this.props.modalId) {
                appService.modalShow(this.props.modalId);
            }else{
                appService.modalShow();
            }
        }
    }
}
</script>
