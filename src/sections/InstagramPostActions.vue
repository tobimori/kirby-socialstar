<script setup>
import { section } from "kirbyuse/props"
const props = defineProps(section)
</script>

<template>
	<k-button class="star-instagram-post-actions" icon="refresh" variant="filled">
		{{ $t("socialstar.refresh") }}
	</k-button>
</template>

<style lang="scss">
.k-button.star-instagram-post-actions {
	width: 100%;
	margin-bottom: var(--spacing-6);
}
</style>
