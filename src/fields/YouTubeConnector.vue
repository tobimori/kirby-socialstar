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

const isLoading = ref(false)
const refresh = () => {
	isLoading.value = true
	panel.api.get(`${panel.view.path}/fields/${props.name}/update`).then(() => {
		isLoading.value = false
		panel.reload()
	})
}
</script>

<template>
	<ConnectorBase
		service="youtube"
		:isConnected="!!userDetails"
		:hasAuthCredentials="hasAuthCredentials"
		:isLoading="isLoading"
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
