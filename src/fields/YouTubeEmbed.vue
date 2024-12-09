<script setup>
import { label } from "kirbyuse/props"

const props = defineProps({
	label: String,
	value: String
})
</script>

<template>
	<k-field v-bind="props">
		<div class="star-youtube-embed">
			<iframe
				:src="`https://www.youtube-nocookie.com/embed/${value}`"
				frameborder="0"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
				allowfullscreen
			></iframe>
		</div>
	</k-field>
</template>

<style lang="scss">
.star-youtube-embed {
	width: 100%;
	height: 0;
	padding-bottom: 56.25%;
	position: relative;
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	overflow: hidden;
	background: var(--item-color-back);

	iframe {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}
}
</style>
