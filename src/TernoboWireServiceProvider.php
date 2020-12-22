<?php
namespace Ternobo\TernoboWire;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ternobo\TernoboWire\Http\Controllers\WireController;
use Ternobo\TernoboWire\Http\Middleware\DataShareMiddleware;

class TernoboWireServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . "resources" => resource_path(),
        ]);

        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(DataShareMiddleware::class);

        Route::prefix("/ternobo-wire")->group(function () {
            Route::post("/get-user", [WireController::class, 'getUser']);
        });
    }
}
