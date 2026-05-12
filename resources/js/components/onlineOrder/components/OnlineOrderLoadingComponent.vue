<template>
    <component :is="loadingComponent" v-if="loadingComponent" spinner="bar-fade-scale" color="#696cff" :active="props.isActive" :is-full-screen="true"/>
</template>

<script>
// import VueElementLoading from 'vue-element-loading';

export default {
    name: "OnlineOrderLoadingComponent",
    components: {
        // VueElementLoading
    },
    props: ['props'],
    data() {
        return {
            loadingComponent: null
        };
    },
    mounted() {
        // Lazy load VueElementLoading
        import('vue-element-loading').then((module) => {
            this.loadingComponent = module.default;
        }).catch(err => {
            console.error('Failed to load VueElementLoading:', err);
        });
    }
}
</script>
