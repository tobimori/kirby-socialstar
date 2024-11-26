<script setup>
import { ref, usePanel } from "kirbyuse"
import { section } from "kirbyuse/props"
const props = defineProps(section)

const panel = usePanel()
const isLoading = ref(false)
const reload = () => {
	isLoading.value = true
	panel.api
		.get(`${panel.view.path}/sections/${props.name}/refresh-data`)
		.then(() => {
			panel.reload()
			isLoading.value = false
		})
}
</script>

<template>
	<k-button
		class="star-instagram-post-actions"
		:disabled="isLoading"
		:icon="isLoading ? 'loader' : 'refresh'"
		variant="filled"
		@click="reload"
	>
		{{ $t("socialstar.refresh") }}
	</k-button>
</template>

<style lang="scss">
.k-button.star-instagram-post-actions {
	width: 100%;
	margin-bottom: var(--spacing-6);
}
</style>
