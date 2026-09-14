<template>
    <PublishForm
        icon="security-lock"
        title="Cookie Consent"
        :blueprint="blueprint"
        :initial-values="values"
        :initial-meta="meta"
        :submit-url="submitUrl"
        submit-method="post"
    />
</template>

<script>
import { PublishForm } from '@statamic/cms/ui';

export default {
    components: { PublishForm },

    props: {
        blueprint: { type: Object, required: true },
        values: { type: Object, required: true },
        meta: { type: Object, required: true },
        submitUrl: { type: String, required: true },
    },
};
</script>
