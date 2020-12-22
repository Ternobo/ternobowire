<?php
namespace Ternobo\TernoboWire;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class TernoboWire
{

    private static $sharedData = [];
    private static $shareFunction;
    public static $ssr = true;

    public static function share(Closure $funtion)
    {
        self::$shareFunction = $funtion;
    }

    public static function setSSR($ssr)
    {
        self::$ssr = $ssr;
    }

    public static function render($component, $data = [])
    {
        $isWireRequest = Request::header('X-TernoboWire');
        self::$sharedData = (self::$shareFunction)();
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        if (is_array(self::$sharedData)) {
            $data = array_merge($data, self::$sharedData);
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

        if ($isWireRequest != null && $isWireRequest) {
            return response()->json($response)->withHeaders(['X-TernoboWire' => true]);
        }
        $ssr = '<div id="app"></div>';
        if (class_exists("V8Js") && self::$ssr) {
            $renderer_source = file_get_contents(base_path('node_modules/vue-server-renderer/basic.js'));
            $app_source = file_get_contents(public_path('js/entry-server.js'));

            $v8 = new \V8Js();
            ob_start();
            $v8->executeString("var process = { env: { VUE_ENV: 'server', NODE_ENV: 'production' }};" .
                "let ternoboApplicationData = " . json_encode($response) . ";" .
                "this.global = { process: process };");
            $v8->executeString($renderer_source);
            $v8->executeString($app_source);
            $ssr = ob_get_clean();
        }
        return view('app', ['ternoboApp' => $ssr, 'ternoboScripts' => "<script>window.ternoboApplicationData = " . json_encode($response) . ";</script>"]);
    }

}
