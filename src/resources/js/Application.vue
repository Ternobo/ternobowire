<template>
	<div id="app" v-if="ready">
		<component :is="layout">
			<component :is="componentInstance" v-bind="propsToBind"></component>
		</component>
	</div>
</template>

<script>
import AppLayout from "./Layouts/AppLayout";
import TernoboWire from "./TernoboWire/TernoboWire";
export default {
	methods: {
		updateComponent() {
			this.resolveComponent(this.component).then((value) => {
				this.componentInstance = value.default;
				if (this.componentInstance.layout != null) {
					this.layout = this.componentInstance.layout;
				}
				if (this.componentInstance.props) {
					Object.keys(this.componentInstance.props).forEach((item) => {
						this.propsToBind[item] = this.data[item];
					});
				}
				this.ready = true;
			});
		},
	},
	data() {
		return {
			propsToBind: {},
			component: null,
			componentInstance: null,
			layout: AppLayout,
			ready: false,
			data: {},
		};
	},
	created() {
		this.data = this.initialData;
		this.component = this.initialComponent;
		this.$store.commit("userUpdate");
		this.$store.commit("setupApp", { data: this.data, app: this });
		this.updateComponent();
	},
	props: ["initialData", "resolveComponent", "initialComponent"],
};
</script>

<style>
</style>
