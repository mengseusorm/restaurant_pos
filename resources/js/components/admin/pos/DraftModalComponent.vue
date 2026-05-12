<template>
    <LoadingComponent :props="loading" />
    <div id="draftModal" class="modal ff-modal">
        <div class="modal-dialog max-w-[340px] rounded-md" id="print"  >
            <div class="modal-header hidden-print"> 
                <h1 class="text-xl font-bold">
                    {{ $t('label.suspend') }}
                </h1> 
                <button class="modal-close lab-close-circle-line font-fill-danger lab-font-size-24"
                    @click="closeModal()">
                </button> 
            </div> 
            <div class="modal-body">
                <div ref="htmlContent" id="receipt-container"> 
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading w-8">
                                    {{ $t('label.qty') }}
                                </th>
                                <th scope="col" class="py-1 font-normal text-xs capitalize flex items-center justify-between text-heading">
                                    <span>{{ $t('label.item_description') }}</span>
                                </th>
                                <th scope="col" class="py-1 font-normal text-xs capitalize text-right justify-between text-heading">
                                    <span>{{ $t('label.price') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>  
                            <tr v-for="item in itemDraft" :key="item"> 
                                <td class="text-left font-normal align-top py-1">
                                    <span class="text-xs leading-5 text-heading">
                                        {{ item.quantity }} 
                                    </span>
                                </td>
                                <td class="text-left font-normal align-top py-1">
                                    <span class="text-xs leading-5 text-heading">
                                        {{ item.name }} 
                                    </span> 
                                </td>
                                <td class="text-right font-normal align-top py-1">
                                    <span class="text-xs leading-5 text-heading">
                                        {{ branch.currency_id.symbol }} {{ item.convert_price }} 
                                    </span>
                                </td>
                            </tr> 
                        </tbody> 
                    </table> 
                </div>
            </div>
            <div class="modal-footer hidden-print">
                <button type="button" v-if="carts.length === 0 || carts.length === null" @click="restore(itemDraft)" class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#1AB759]">
                   <span class="text-xs leading-5 capitalize text-white">
                       {{ $t('button.restore') }} 
                   </span> 
                </button> 
            </div>
        </div>
    </div>   
</template>

<script> 
import alertService from '../../../services/alertService';
import appService from '../../../services/appService'; 
import LoadingComponent from "../components/LoadingComponent";

export default {
    name: 'DraftModalComponent',
    components:{
        LoadingComponent, 
    },
    props: {
        itemDraft: {
            type: Object,
            required: true,
        },
        itemIndex: null
    },
    data() {
        return { 
            loading: {
                isActive: false,
            },
        };
    },
    computed: { 
        saveDrafts: function () {
            return this.$store.getters["posCartSaveDraft/lists"];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        branches: function () { 
            return this.$store.getters["branch/lists"];
        },
        carts: function () {
            return this.$store.getters['posCart/lists'];
        },

    },  
    methods: {  
        reset: function () {
            appService.modalHide('#draftModal');
        }, 
        restore:function(itemDraft){  
            this.$store.dispatch("posCart/lists", itemDraft).then((res) => {
                alertService.success(this.$t('message.suspend'));
                this.$store.dispatch("posCartSaveDraft/removeFromDraft", this.itemIndex).then((res) => {
                    this.reset()
                }).catch(); 
            }).catch(); 
        }, 
        closeModal: function () {
            appService.modalHide('#draftModal');
        },
    }, 
};
</script>