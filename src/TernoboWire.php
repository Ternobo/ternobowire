<?php
namespace Ternobo\TernoboWire;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
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
            Route::post("/get-shared", [WireController::class, 'getShared']);
            Route::post("/get-data/{token}", [WireController::class, 'getData'])->where('token', '.*');;
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

    private static function uuidv4($prefix = '')
    {
        return sprintf($prefix . '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Render Page
     *
     * @param String $component Page Component Name
     * @param Array $data Data that will be passed to Page
     */
    public static function render($component, $data = [], $json = false, $status = 200, $headers = [])
    {
        $tools = new WireTools();
        $isWireRequest = $json ? '1' : Request::header('X-TernoboWire');
        self::$sharedData = (self::$shareFunction)();
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
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
            return response()->json($response)->withHeaders(['X-TernoboWire' => true, "Pragma" => "no-cache", "Cache-Control" => "no-cache, no-store, must-revalidate"]);
        }
        $ssr = '<div id="app"></div>';
        if (class_exists("V8Js") && self::$ssr) {
            $renderer_source = file_get_contents(base_path('node_modules/vue-server-renderer/basic.js'));
            $app_source = file_get_contents(public_path('js/entry-server.js'));
            $ssr = $tools->serversideRender($renderer_source, $app_source, $response);
        }

        $cacheId = session()->getId() . self::uuidv4("ternobo_wire_");
        Cache::put("$cacheId", json_encode($response));
        return Response::view('app', ['ternoboApp' => $ssr, 'tuuid' => $cacheId], $status, $headers);
    }

}
