<?php
namespace Ternobo\TernoboWire;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
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

    }

    public function register()
    {
        // $this->app->singleton(TernoboWire::class, function () {
        //     return new TernoboWire();
        // });
    }
}
