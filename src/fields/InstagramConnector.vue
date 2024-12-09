<script setup>
import { ref, usePanel } from "kirbyuse"
import { name } from "kirbyuse/props"
import ConnectorBase from "./ConnectorBase.vue"

const props = defineProps({
	hasAuthCredentials: Boolean,
	authUrl: String,
	userDetails: Object,
	...name
})

const openAuthUrl = () => {
	window.location.href = props.authUrl
}

const panel = usePanel()

const isLoading = ref(false)
const loadNewPosts = () => {
	isLoading.value = true
	panel.api.get(`${panel.view.path}/fields/${props.name}/update`).then(() => {
		isLoading.value = false
		panel.reload()
	})
}

const disconnect = () => {
	panel.dialog.open({
		component: "k-remove-dialog",
		props: { text: panel.t("socialstar.instagram.disconnectConfirm") },
		on: {
			submit: () => {
				panel.api
					.get(`${panel.view.path}/fields/${props.name}/remove-auth`)
					.then(() => {
						panel.reload()
					})

				panel.dialog.close()
			}
		}
	})
}
</script>

<template>
	<ConnectorBase
		service="instagram"
		:isConnected="!!userDetails"
		:hasAuthCredentials="hasAuthCredentials"
		:isLoading="isLoading"
		@connect="openAuthUrl"
		@disconnect="disconnect"
		@refresh="loadNewPosts"
	>
		<template v-if="userDetails">
			<img
				:src="userDetails.profile_picture_url"
				class="star-instagram_avatar"
			/>

			<div class="star-instagram_content">
				<a
					class="star-instagram_name"
					:href="`https://instagram.com/${userDetails.username}`"
					target="_blank"
				>
					<span>{{ userDetails.name }}</span>
					<strong>@{{ userDetails.username }}</strong>
				</a>

				<ul>
					<li>
						<k-icon type="images" />

						{{
							$t("socialstar.instagram.posts", {
								count: userDetails.media_count
							})
						}}
					</li>

					<li>
						<k-icon type="users" />

						{{
							$t("socialstar.instagram.followers", {
								count: userDetails.followers_count
							})
						}}
					</li>

					<li>
						<k-icon type="star-outline" />

						{{
							$t("socialstar.instagram.follows", {
								count: userDetails.follows_count
							})
						}}
					</li>
				</ul>
			</div>
		</template>
	</ConnectorBase>
</template>

<style lang="scss">
.star-instagram {
	background: var(--item-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded-lg);
	padding: var(--spacing-1);
	display: flex;
	gap: var(--spacing-3);
	align-items: center;

	&_avatar {
		width: 4rem;
		height: 4rem;
		border-radius: var(--rounded);
		object-fit: cover;
	}

	&_name {
		display: flex;
		gap: var(--spacing-1);

		@container (max-width: 40em) {
			flex-direction: column;
		}
	}

	&_content {
		display: flex;
		flex-direction: column;
		gap: 0.625rem;

		ul,
		li {
			display: flex;
			gap: var(--spacing-1);
			align-items: center;
			font-weight: var(--font-semi);
			color: var(--color-text-dimmed);

			@container (max-width: 40em) {
				display: none;
			}
		}

		li:not(:last-child)::after {
			opacity: 0.5;
			content: "·";
			margin: 0 var(--spacing-1);
		}
	}

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
