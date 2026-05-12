// Global computed properties mixin
export default {
    computed: {
        language_code() {
            const globalState = this.$store.getters['globalState/get'];
            const code = globalState?.language_code || 'en';
            console.log('Global mixin language_code:', code);
            return code;
        },
        branch_id() {
            const globalState = this.$store.getters['globalState/get'];
            const id = globalState?.branch_id || 1;
            console.log('Global mixin branch_id:', id);
            return id;
        }
    }
}
