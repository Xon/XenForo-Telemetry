<?php

class SV_Telemetry_Model
{
    public static $lastControllerKey = null;

    protected static $logger = null;
    protected static $hostname = null;

    public static function setupLogger($host = "unix:///var/run/td-agent/td-agent.sock", $port = 0)
    {
        if (self::$logger !== null)
        {
            return;
        }
        self::$hostname = gethostname();
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

        self::$logger->post('xf.'.$key, array(
            'hostname' => self::$hostname,
            'content_type' => $contentType,
            "content_id" => $contentId,
            "@timestamp" => date("c", $startTime),
            "duration" =>($endTime - $startTime)
        ));
    }

    private function __construct() {}
}