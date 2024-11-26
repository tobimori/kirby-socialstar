import "./index.scss"

import InstagramConnector from "./sections/InstagramConnector.vue"
import InstagramPostActions from "./sections/InstagramPostActions.vue"

panel.plugin("tobimori/socialstar", {
	sections: {
		"socialstar-instagram-post-actions": InstagramPostActions
	},
	fields: {
		"socialstar-instagram-connector": InstagramConnector
	}
})
