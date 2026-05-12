<template>
    <div v-if="templateId === 1">
        <ContactUsComponent />
    </div>
</template>

<script>

import TelegramMiniAppContactUsComponent from "../page/TelegramMiniAppContactUsComponent";
export default {
    name: "TelegramMiniAppTemplateManagerComponent",
    props: ["templateId"],
    components: { TelegramMiniAppContactUsComponent }
}
</script>