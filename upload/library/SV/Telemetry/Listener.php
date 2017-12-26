<?php

class SV_Telemetry_Listener
{
    static $lastControllerKey;

    public static function load_class($class, array &$extend)
    {
        $extend[] = 'SV_Telemetry_' . $class;
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
            SV_Telemetry_Wrapper::stats()->timing('xenforo.action', microtime(true) - $controller->telemetry_starttime, 1, ['tagname' => self::$lastControllerKey]);
        }
    }

    public static function pre_view(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
    {
        $fc->telemetry_starttime = microtime(true);
    }

    public static function post_view(XenForo_FrontController $fc, &$output)
    {
        global $supressTelemetryBufferFlush;
        if (!empty($fc->telemetry_starttime))
        {
            SV_Telemetry_Wrapper::stats()->timing('xenforo.view', microtime(true) - $fc->telemetry_starttime, 1, ['tagname' => self::$lastControllerKey]);
        }
        if (!empty($supressTelemetryBufferFlush))
        {
            SV_Telemetry_Wrapper::stats()->flush_buffer();
        }
    }
}
