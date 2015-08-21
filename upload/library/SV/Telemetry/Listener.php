<?php

class SV_Telemetry_Listener
{
    const AddonNameSpace = 'SV_Telemetry_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }

    public static function pre_dispatch(XenForo_Controller $controller, $action, $controllerName)
    {
        SV_Telemetry_Model::$lastControllerKey = $controllerName. '.'. $action;
        $controller->telemetry_starttime = microtime(true);
    }

    public static function post_dispatch(XenForo_Controller $controller, $controllerResponse, $controllerName, $action)
    {
        SV_Telemetry_Model::$lastControllerKey = $controllerName. '.'. $action;
        SV_Telemetry_Model::postTiming(SV_Telemetry_Model::$lastControllerKey, $controller->telemetry_starttime, microtime(true));
    }

    public static function pre_view(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
    {
        $fc->telemetry_starttime = microtime(true);
    }

    public static function post_view(XenForo_FrontController $fc, &$output)
    {
        SV_Telemetry_Model::postTiming('view.'SV_Telemetry_Model::$lastControllerKey, $fc->telemetry_starttime, microtime(true));
    }
}