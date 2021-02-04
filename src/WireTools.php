<?php
namespace Ternobo\TernoboWire;

class WireTools
{
    /**
     * Run ServerSide Render, Using V8Js Engine
     *
     * Default Render Source base_path('node_modules/vue-server-renderer/basic.js')
     * @param String $renderSource Vue Server Renderer Path
     * @param String $appSource Application ServerSide entry Path
     * @param Array $applicationData Application Data
     */
    public function serversideRender($renderSource, $appSource, $applicationData)
    {
        $renderer_source = file_get_contents($renderSource);
        $app_source = file_get_contents($appSource);
        $v8 = new \V8Js();
        ob_start();
        $v8->executeString("var process = { env: { VUE_ENV: 'server', NODE_ENV: 'production' }};" .
            "let ternoboApplicationData = " . json_encode($response) . ";" .
            "this.global = { process: process };");
        $v8->executeString($renderer_source);
        $v8->executeString($app_source);
        return ob_get_clean();
    }

    public function handleReload($data, $shared = [], $options = [])
    {
        $shared = array_map(function ($value) {
            if (is_callable($value)) {
                return ($value)();
            }
            return $value;
        }, $share);

        return [
            'data' => $data,
            'shared' => $shared,
        ];
    }
}
