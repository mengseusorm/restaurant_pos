<template>
    <div class="therapist-app-wrapper">
        <TherapistNavbarComponent />
        <router-view />
    </div>
</template>

<script>
import TherapistNavbarComponent from './TherapistNavbarComponent.vue';

export default {
    name: "TherapistAppComponent",
    components: {
        TherapistNavbarComponent,
    },
};
</script>

<style scoped>
.therapist-app-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
</style>
