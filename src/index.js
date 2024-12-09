import "./index.scss"

import InstagramConnector from "./fields/InstagramConnector.vue"
import YoutubeConnector from "./fields/YoutubeConnector.vue"
import YoutubeEmbed from "./fields/YouTubeEmbed.vue"
import Refresh from "./view-buttons/Refresh.vue"

panel.plugin("tobimori/socialstar", {
	fields: {
		"socialstar-instagram-connector": InstagramConnector,
		"socialstar-youtube-connector": YoutubeConnector,
		"socialstar-youtube-embed": YoutubeEmbed
	},
	viewButtons: {
		"socialstar-refresh": Refresh
	}
})
