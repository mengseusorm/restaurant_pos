<template>
    <div v-if="templateId === 1">
        <ContactUsComponent />
    </div>
</template>

<script>

import OnlineOrderContactUsComponent from "../page/OnlineOrderContactUsComponent";
export default {
    name: "OnlineOrderTemplateManagerComponent",
    props: ["templateId"],
    components: { OnlineOrderContactUsComponent }
}
</script>
