# Ternobo Wire

> Use ServerSide Routing, And server-driven data sharing in VueJs

## How To Use

Blade Template :

```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Wire Application') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <!-- Scripts -->
    {!! $ternoboScripts !!}
</head>

<body class="font-sans antialiased">
    {!! $ternoboApp !!}
    <script src="{{ mix('js/entry-client.js') }}" defer></script>

</body>
</html>
```

### server-side rendering

#### entry-client.js

```javascript
import app from "./app";
app().$mount("#app");
```

#### entry-server.js :

```javascript
import app from "./app";
let application = app(true, data, component).$mount();
renderVueComponentToString(application, (err, res) => {
	print(res);
});
```

#### app.js

```javascript
require("./bootstrap");

require("moment");

import Vue from "vue";
import TernoboApp from "./Application.vue";
import { store } from "./TernoboWire/TernoboWire";
import { plugin } from "./TernoboWire/TernoboWire";

export default function (ssr = false, data = null, component = null) {
	let appInstance = null;
	Vue.use(plugin);
	let vuexStore = store();

	if (ssr) {
		appInstance = new Vue({
			store: vuexStore,
			render: (h) =>
				h(TernoboApp, {
					props: {
						initialData: data,
						initialComponent: component,
						resolveComponent: (component) => import(`./Pages/${component}`),
					},
				}),
		});
	} else {
		let instanceData = window.ternoboApplicationData;
		component = instanceData.component;

		appInstance = new Vue({
			store: vuexStore,
			render: (h) =>
				h(TernoboApp, {
					props: {
						initialData: instanceData.data,
						initialComponent: component,
						resolveComponent: (component) => import(`./Pages/${component}`),
					},
				}),
		});
	}
	return appInstance;
}
```
