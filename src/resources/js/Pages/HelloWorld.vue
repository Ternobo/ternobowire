<template>
	<div>
		<wire-link href="/test">{{ name }}</wire-link>
	</div>
</template>

<script>
import WireLink from "../Components/WireLink";
export default {
	components: {
		WireLink,
	},
	props: {
		name: {
			type: String,
			default: undefined,
		},
	},
};
</script>
