<script setup></script>

<template>
	<k-button
		:disabled="isLoading"
		:icon="isLoading ? 'loader' : 'refresh'"
		variant="filled"
		@click="reload"
	>
		{{ $t("socialstar.refresh") }}
	</k-button>
</template>
