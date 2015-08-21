<?php

class SV_Telemetry_Model
{
    public static $lastControllerKey = null;

    protected static $logger = null;

    public static function setupLogger($host = "unix:///var/run/td-agent/td-agent.sock", $port = 0)
    {
        if (self::$logger !== null)
        {
            return;
        }
        require_once __DIR__.'/Fluent/Autoloader.php';
        Fluent\Autoloader::register();
        self::$logger = new \Fluent\Logger\FluentLogger($host, $port);
        Fluent\Autoloader::unregister();
    }

    public static function postTiming($key, $startTime, $endTime, $contentType = null, $contentId = null)
    {
        if (self::$logger === null)
        {
            self::setupLogger(XenForo_Application::getOptions()->sv_telemetry_fluent_connection);
        }

        self::$logger->post($key, array('_type' => $contentType, "_id" => $contentId, "start"=>$startTime, "end"=>$endTime));
    }

    private function __construct() {}
}