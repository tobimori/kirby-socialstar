<script setup>
import { ref, usePanel } from "kirbyuse"
import { name } from "kirbyuse/props"
import ConnectorBase from "./ConnectorBase.vue"

const props = defineProps({
	hasAuthCredentials: Boolean,
	userDetails: Object,
	...name
})
console.log(props.userDetails)

const panel = usePanel()
const connect = () => {
	panel.dialog.open({
		component: "k-form-dialog",
		props: {
			fields: {
				username: {
					label: panel.t("socialstar.youtube.connectUser"),
					before: "@",
					required: true,
					type: "text"
				}
			}
		},
		on: {
			submit: (values) => {
				panel.api
					.post(`${panel.view.path}/fields/${props.name}/connect`, values)
					.then(() => {
						panel.reload()
					})

				panel.dialog.close()
			}
		}
	})
}

const disconnect = () => {
	panel.dialog.open({
		component: "k-remove-dialog",
		props: { text: panel.t("socialstar.youtube.disconnectConfirm") },
		on: {
			submit: () => {
				panel.api
					.post(`${panel.view.path}/fields/${props.name}/disconnect`)
					.then(() => {
						panel.reload()
					})

				panel.dialog.close()
			}
		}
	})
}

const currentPage = ref(null)
const refresh = async () => {
	currentPage.value = 1
	const previousTokens = []
	let pageToken = null
	while (pageToken !== undefined) {
		if (pageToken) {
			previousTokens.push(pageToken)
		}

		try {
			const request = await panel.api.post(
				`${panel.view.path}/fields/${props.name}/update`,
				{ pageToken }
			)

			currentPage.value++
			pageToken = request.token ?? undefined
		} catch (error) {
			panel.notification.error(error.message)
		}
	}

	await panel.api.post(`${panel.view.path}/fields/${props.name}/cleanup`, {
		pageTokens: previousTokens
	})

	currentPage.value = null
	panel.reload()
}
</script>

<template>
	<ConnectorBase
		service="youtube"
		:isConnected="!!userDetails"
		:hasAuthCredentials="hasAuthCredentials"
		:currentPage="currentPage"
		:totalPages="
			userDetails ? Math.ceil(userDetails.statistics.videoCount / 20) : 0
		"
		@connect="connect"
		@disconnect="disconnect"
		@refresh="refresh"
	>
		<template v-if="userDetails">
			<img
				:src="userDetails.snippet.thumbnails.high.url"
				class="star-instagram_avatar"
			/>

			<div class="star-instagram_content">
				<a
					class="star-instagram_name"
					:href="`https://youtube.com/${userDetails.snippet.customUrl}`"
					target="_blank"
				>
					<span>{{ userDetails.snippet.title }}</span>
					<strong>{{ userDetails.snippet.customUrl }}</strong>
				</a>

				<ul>
					<li>
						<k-icon type="youtube" />

						{{
							$t("socialstar.youtube.videoCount", {
								count: userDetails.statistics.videoCount
							})
						}}
					</li>

					<li>
						<k-icon type="users" />

						{{
							$t("socialstar.youtube.subscriberCount", {
								count: userDetails.statistics.subscriberCount
							})
						}}
					</li>
				</ul>
			</div>
		</template>
	</ConnectorBase>
</template>
