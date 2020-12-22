<?php

namespace Ternobo\TernoboWire\Http\Middleware;

use Closure;
use Ternobo\TernoboWire\TernoboWire;

class DataShareMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        return $response;
    }
}
