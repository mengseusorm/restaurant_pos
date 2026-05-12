<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" /> 
    <div id="printer-create-modal" class="modal">
        <div class="modal-dialog max-w-[840px]">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.kitchen_printer') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div> 
            <div class="modal-body">   
                <form @submit.prevent="save">
                    <div class="form-row">  
                        <div class="form-col-12 sm:form-col-12">
                            <label for="branch_id" class="db-field-title required">{{ $t("label.branch") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="branch_id"
                                v-bind:class="errors.branch_id ? 'invalid' : ''"
                                v-model="props.form.branch_id" :options="branches" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
                            <small class="db-field-alert" v-if="errors.branch_id">{{ errors.branch_id[0] }}</small>
                        </div>    
                        <div class="form-col-12">  
                            <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text" id="name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>   
                        <div class="form-col-12">  
                            <label for="print_copies" class="db-field-title required">{{ $t("label.print_copies") }}</label>
                            <input v-model="props.form.print_copies" v-bind:class="errors.print_copies ? 'invalid' : ''" type="text" id="print_copies" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.print_copies">{{ errors.print_copies[0] }}</small>
                        </div>   
                        <div class="form-col-12">  
                            <label for="label" class="db-field-title required">{{ $t("label.label") }}</label>
                            <input v-model="props.form.label" v-bind:class="errors.label ? 'invalid' : ''" type="text" id="label" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.label">{{ errors.label[0] }}</small>
                        </div>   
                        <div class="form-col-12 sm:form-col-12">
                            <label class="db-field-title required" for="active">{{ $t('label.printer_type') }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.printerTypeEnum.PRINTINVOICE" v-model="props.form.printer_type" id="print_receipt_active" type="radio" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="print_receipt" class="db-field-label">{{ $t('label.print_invoice') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.printerTypeEnum.PRINTMENU" v-model="props.form.printer_type" type="radio" id="print_menu_active" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="print_menu" class="db-field-label">{{ $t('label.print_menu') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.printerTypeEnum.PRINTBILL" v-model="props.form.printer_type" type="radio" id="print_menu_bill" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="print_menu" class="db-field-label">{{ $t('label.print_bill') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.printerTypeEnum.PRINTLABEL" v-model="props.form.printer_type" type="radio" id="print_menu_active" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="print_menu" class="db-field-label">{{ $t('label.print_label') }}</label>
                                </div>
                            </div>
                        </div> 
                        <div class="form-col-12 sm:form-col-12">
                            <label class="db-field-title required" for="active">{{ $t('label.printer_method') }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">  
                                        <input :value="enums.printerMethodEnum.IP" v-model="props.form.printer_method"   type="radio" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="print_receipt" class="db-field-label">{{ $t('label.ip') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.printerMethodEnum.USB" v-model="props.form.printer_method"  type="radio" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="print_receipt" class="db-field-label">{{ $t('label.usb') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.printerMethodEnum.WEBPRINT" v-model="props.form.printer_method" type="radio" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="print_menu" class="db-field-label">{{ $t('label.webprint') }}</label>
                                </div> 
                            </div> 
                        </div>  
                        <div class="form-col-12">  
                            <label for="name" class="db-field-title required">{{ $t("label.printer_server") }}</label>
                            <input v-model="props.form.printer_server" v-bind:class="errors.printer_server ? 'invalid' : ''" type="text" id="name" class="db-field-control">
                            <!-- <small class="db-field-alert" v-if="errors.name">{{ errors.printer_server[0] }}</small> -->
                        </div>  
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name" class="db-field-title">{{ $t("label.ip_printer") }}</label>
                            <input v-model="props.form.ip" type="text" id="ip" class="db-field-control">
                        </div>  
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name" class="db-field-title">{{ $t("label.port") }}</label>
                            <input v-model="props.form.port" type="text" id="port" class="db-field-control">
                        </div>  
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t('button.close') }}</span>
                                </button> 
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t('button.save') }}</span>
                                </button>
                            </div>
                        </div> 
                    </div>
                </form>
            </div>
        </div>
    </div> 
</template>
<script>
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import printerTypeEnum from "../../../../enums/modules/printerTypeEnum";
import printerMethodEnum from "../../../../enums/modules/printerMethodEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";

export default {
    name: "PrinterCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            errors: {}, 
            enums : {
                printerTypeEnum:printerTypeEnum,
                printerMethodEnum:printerMethodEnum
            }
        }
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_printer') };
        },
        branches: function () { 
            return this.$store.getters["branch/lists"];
        },
        authBranch: function () {
            return this.$store.getters.authBranchId;
        },
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
    },
    mounted() { 
        this.$store.dispatch("defaultAccess/show");
    },
    methods: { 
        reset: function () {
            appService.modalHide(); 
            this.errors = {};
            this.$props.props.form = {
                name: "",
                ip: "",
                port: "", 
                printer_type: this.enums.printerTypeEnum.PRINTINVOICE,
                printer_method: this.enums.printerMethodEnum.IP,
                printer_server: "",
                branch_id:null, 
                label:"",
                print_copies:1
            } 
        },

        save: function () { 
            try {  
                const data = {
                    name:this.props.form.name,
                    ip:this.props.form.ip,
                    port:this.props.form.port,
                    printer_type: this.props.form.printer_type,
                    printer_method: this.props.form.printer_method, 
                    printer_server: this.props.form.printer_server,
                    branch_id: this.props.form.branch_id,
                    label:this.props.form.label, 
                    print_copies:this.props.form.print_copies
                }     
                const tempId = this.$store.getters['printer/temp'].temp_id;
                this.loading.isActive = true; 
                this.$store.dispatch('printer/save', {
                    form: data, 
                }).then((res) => {  
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('message.printer'));
                    this.props.form = {
                        name: "",
                        ip: "",
                        port: "",
                        printer_type: null,
                        printer_method: null,
                        printer_server: "",
                        branch_id:null,
                        label:null,
                        print_copies:1
                    } 
                    this.errors = {}; 
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors; 
                }) 
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err)
            }
        }
    }
}
</script>