<?php

class SV_Telemetry_Listener
{
    const AddonNameSpace = 'SV_Telemetry_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }

    public static $lastControllerKey = null;
    public static $lastControllerIds = null;

    public static function pre_dispatch(XenForo_Controller $controller, $action, $controllerName)
    {
        $controller->telemetry_starttime = microtime(true);
    }

    public static function post_dispatch(XenForo_Controller $controller, $controllerResponse, $controllerName, $action)
    {
        self::$lastControllerKey = $controllerName. '::'. $action;
        self::$lastControllerIds = array();
        SV_Telemetry_Globals::addTiming('controller', self::$lastControllerKey, $controller->telemetry_starttime, microtime(true));
    }


    public static function pre_view(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
    {
        $fc->telemetry_starttime = microtime(true);
    }

    public static function post_view(XenForo_FrontController $fc, &$output)
    {
        SV_Telemetry_Globals::addTiming('view', self::$lastControllerKey, self::$lastControllerIds, $fc->telemetry_starttime, microtime(true));
    }
}