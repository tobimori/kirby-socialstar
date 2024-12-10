<script setup>
import { name } from "kirbyuse/props"

const props = defineProps({
	hasAuthCredentials: Boolean,
	isConnected: Boolean,
	service: String,
	currentPage: {
		type: Number,
		default: null
	},
	totalPages: Number,
	...name
})

const emit = defineEmits(["connect", "disconnect", "refresh"])
</script>

<template>
	<k-field
		class="star-connect-account"
		v-if="!isConnected"
		:class="{ 'has-error': !hasAuthCredentials }"
		v-bind="props"
	>
		<div class="star-connect-account_wrapper">
			<k-icon :type="service" />
			<span v-if="hasAuthCredentials">{{
				$t(`socialstar.${service}.connectAccount`)
			}}</span>
			<span v-else>{{
				$t(`socialstar.${service}.missingApiCredentials`)
			}}</span>
		</div>

		<k-button
			size="sm"
			variant="filled"
			icon="check"
			:theme="hasAuthCredentials ? 'pink' : 'error'"
			:disabled="!hasAuthCredentials"
			@click="emit('connect')"
		>
			{{ $t("socialstar.connect") }}
		</k-button>
	</k-field>
	<k-field v-else :label="$t(`socialstar.${service}.account`)">
		<div class="star-account">
			<slot />

			<div class="star-account_actions">
				<k-button
					size="sm"
					theme="green"
					variant="filled"
					:icon="!!currentPage ? 'loader' : 'download'"
					:disabled="!!currentPage"
					@click="emit('refresh')"
				>
					{{
						currentPage
							? $t("socialstar.loadingPage", { currentPage, totalPages })
							: $t("socialstar.loadNewPosts")
					}}
				</k-button>
				<k-button
					size="sm"
					variant="filled"
					theme="error"
					icon="logout"
					@click="emit('disconnect')"
				>
					{{ $t("socialstar.disconnect") }}
				</k-button>
			</div>
		</div>
	</k-field>
</template>

<style lang="scss">
.star-account {
	background: var(--item-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded-lg);
	padding: var(--spacing-1);
	display: flex;
	gap: var(--spacing-3);
	align-items: center;

	&_actions {
		display: flex;
		flex-direction: column;
		margin-left: auto;
		gap: var(--spacing-1);
		margin-right: 0.1rem;

		.k-button {
			min-width: 13em;
			justify-content: start;
		}
	}
}

.star-connect-account {
	background: var(--color-pink-300);
	padding: var(--spacing-1);
	border-radius: var(--rounded-lg);
	justify-content: space-between;
	color: var(--color-black);

	&.has-error {
		background: var(--color-red-300);
		color: var(--color-red-900);
	}

	.k-field-header {
		display: none;
	}

	&,
	&_wrapper {
		display: flex;
		align-items: center;
	}

	&_wrapper {
		padding-left: var(--spacing-2);
		gap: var(--spacing-2);
	}
}
</style>
