<template>
    <panel-item :field="field">
        <template slot="value">
            <div class="relative overflow-auto border border-40" :style="{ height: field.height }">
                <div v-if="loading" class="flex justify-center items-center absolute pin z-50 bg-white">
                    <loader class="text-60" />
                </div>
                <template v-else>
                    <div v-if="! resources.length" class="flex items-center justify-center h-full">
                        <span class="text-70 text-xl">Not linked with any {{ field.attribute }}</span>
                    </div>

                    <div v-else v-for="resource in resources" :key="resource.value" class="flex items-center justify-between px-3 py-3 select-none hover:bg-30">
                        <span class="flex items-center">
                            <img
                                v-if="resource.previewImg"
                                :src="resource.previewImg"
                                class="w-8 h-8 rounded-full shadow object-cover mr-4"
                            >
                            <span>
                                <a :href="resource.novaUrl" target="_blank" class="no-underline dim text-primary font-bold">
                                    {{ resource.display }}
                                </a>
                                <span v-if="resource.detailAttribute" class="text-60">
                                    ({{ resource.detailAttribute }})
                                </span>
                            </span>
                        </span>
                        <span class="text-right text-60">
                            #{{ resource.value }}
                        </span>
                    </div>
                </template>
            </div>
            <div class="help-text mt-3 w-full flex justify-between" :class="{ 'invisible': loading }">
                <span v-if="field.showCounts && selectedIds.length" class="pr-2 border-60 whitespace-no-wrap" :class="{ 'border-r mr-2': field.helpText }">
                    {{ selectedIds.length  }} / {{ available.length }}
                </span>
            </div>
        </template>
    </panel-item>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],

    data() {
        return {
            selectedIds: [],
            available: [],
            loading: true,
        }
    },

    computed: {
        resources() {
            if (! this.available.length) {
                return []
            }

            return this.available.filter((resource) => {
                return this.selectedIds.includes(resource.value)
            });
        }
    },

    methods: {
        setValue() {
            let baseUrl = '/nova-vendor/nova-attach-many/';

            Nova.request(baseUrl + this.resourceName + '/' + this.resourceId + '/attachable/' + this.field.attribute)
                .then((data) => {
                    this.selectedIds = data.data.selected || [];
                    this.available = data.data.available || [];
                    this.loading = false;
                });
        },

        resourceUrl() {

        }
    },

    mounted() {
        this.setValue()
    }
}
</script>
