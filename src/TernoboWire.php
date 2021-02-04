<?php
namespace Ternobo\TernoboWire;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Ternobo\TernoboWire\Http\Controllers\WireController;

class TernoboWire
{

    private static $sharedData = [];
    private static $shareFunction;
    private static $ssr = true;

    /**
     * Ternobo Wire Routes
     * 1 - /ternobo-wire/get-user - return active user
     * 2 - /ternobo-wire/get-token - return current csrf token
     */
    public static function routes()
    {
        Route::prefix("/ternobo-wire")->group(function () {
            Route::post("/get-user", [WireController::class, 'getUser']);
            Route::get("/get-token", [WireController::class, 'getToken']);
        });
    }

    /**
     * Set Shared Data Closure
     *
     * @param Closure $funtion this function should return Maped Array, which run before rendering.
     */
    public static function share(Closure $funtion)
    {
        self::$shareFunction = $funtion;
    }

    /**
     * @param boolval $ssr if true, render function use ServerSide Render if available
     */
    public static function setSSR($ssr)
    {
        self::$ssr = $ssr;
    }

    /**
     * Render Page
     *
     * @param String $component Page Component Name
     * @param Array $data Data that will be passed to Page
     */
    public static function render($component, $data = [])
    {
        $tools = new WireTools();
        $isWireRequest = Request::header('X-TernoboWire');
        self::$sharedData = (self::$shareFunction)();
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $data['user'] = null;

        if (Auth::check()) {
            $data['user'] = Auth::user();
        }

        $response = [
            'data' => $data,
            'component' => $component,
        ];
        if (Auth::check()) {
            $response['user'] = Auth::user();
        }
        if (is_array(self::$sharedData)) {
            $response['shared'] = array_map(function ($value) {
                if (is_callable($value)) {
                    return ($value)();
                }
                return $value;
            }, self::$sharedData);
        }
        if ($isWireRequest != null && $isWireRequest) {
            return response()->json($response)->withHeaders(['X-TernoboWire' => true]);
        }
        $ssr = '<div id="app"></div>';
        if (class_exists("V8Js") && self::$ssr) {
            $renderer_source = file_get_contents(base_path('node_modules/vue-server-renderer/basic.js'));
            $app_source = file_get_contents(public_path('js/entry-server.js'));
            $tools->serversideRender($renderer_source, $app_source, $response);
            $ssr = ob_get_clean();
        }
        return view('app', ['ternoboApp' => $ssr, 'data' => json_encode($response)]);
    }

}
