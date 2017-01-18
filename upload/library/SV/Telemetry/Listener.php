<?php

include_once('SV/Telemetry/DataDog/libraries/datadogstatsd.php');

class SV_Telemetry_Listener
{
    const AddonNameSpace = 'SV_Telemetry_';
    static $lastControllerKey;

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }

    public static function pre_dispatch(XenForo_Controller $controller, $action, $controllerName)
    {
        self::$lastControllerKey = $controllerName;//. '.'. $action;
        $controller->telemetry_starttime = microtime(true);
    }

    public static function post_dispatch(XenForo_Controller $controller, $controllerResponse, $controllerName, $action)
    {
        self::$lastControllerKey = $controllerName;//. '.'. $action;
        if (!empty($controller->telemetry_starttime))
        {
            BatchedDatadogstatsd::timing('xenforo.action', microtime(true) - $controller->telemetry_starttime, 1, array('tagname' => self::$lastControllerKey));
        }
    }

    public static function pre_view(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
    {
        $fc->telemetry_starttime = microtime(true);
    }

    public static function post_view(XenForo_FrontController $fc, &$output)
    {
        if (!empty($fc->telemetry_starttime))
        {
            BatchedDatadogstatsd::timing('xenforo.view', microtime(true) - $fc->telemetry_starttime, 1, array('tagname' => self::$lastControllerKey));
        }
        BatchedDatadogstatsd::flush_buffer();
    }
}