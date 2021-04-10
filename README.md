# Ternobo Wire

> Use ServerSide Routing, And server-driven data sharing in VueJs

## ServerSide Setup

Create app.blade.php inside resources/view, then setup application root and token attribute:

```php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ternobo App</title>
  <link href="/css/app.css" rel="stylesheet" />
</head>

<body class="font-sans antialiased" data-wire='{{ $tuuid }}'>
    {!! $ternoboApp !!}
    <script src="/js/manifest.js" defer></script>
    <script src="/js/vendor.js?" defer></script>
    <script src="/js/app.js" defer></script>
</body>

</html>
```

### Routes
Append TernoboWire::routes(); to top of routes/web.php file.

```php
<?php
use Illuminate\Support\Facades\Route;
use Ternobo\TernoboWire\TernoboWire;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

TernoboWire::routes();
Route::get('/', "IndexController@index");
// ....
```

### Shared data function
The server-side adapters provide a way to preassign shared data for each request. This is typically done outside of your controllers. Shared data will be automatically loaded into shared state(Vuex).
 
Setup shared data function inside App/Providers/AppServiceProvider.php boot function.
```php 
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Ternobo\TernoboWire\TernoboWire;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TernoboWire::share(function () {
            return [
            	"appName" => config('app.name'),
				"is_admin" => function (){
					if(Auth::check()){
						return Auth::user()->is_admin;
					}
					return false;
				}
            ];
        });
    }
}
```
The function returns a mapped array that contains all shared Data. 



##Client-Side

once you have the server-side application configured, you need to setup the client-side application.

###Initialize app
 
Setup your application entry point, then create a folder named Pages inside your front-end application root (default: resources/js).

```javascript
import Vue from "vue";
import WireApp from "wire-js"; 
import { plugin, store } from "wire-js"; 
import Vuex from "vuex";
Vue.use(plugin);
Vue.use(Vuex);

let dataToken = document.body.dataset.wire;
document.body.dataset.wire = "";
const vue_app = new Vue({
    store: store(),
    render: (h) =>
        h(WireApp, {
            props: {
                dataToken: dataToken,
                resolveComponent: (component) => import(`./Pages/${component}`),
            },
        }),
}).$mount("#app");
```

** Now you can create any page and render it using TernoboWrie::render("pagename", $data) **

