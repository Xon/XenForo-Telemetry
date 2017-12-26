<?php

class SV_Telemetry_XenForo_Model_ThreadWatch extends XFCP_SV_Telemetry_XenForo_Model_ThreadWatch
{
    public function sendNotificationToWatchUsersOnReply(array $reply, array $thread = null, array $noAlerts = [])
    {
        $starttime = microtime(true);
        try
        {
            return parent::sendNotificationToWatchUsersOnReply($reply, $thread, $noAlerts);
        }
        finally
        {
            SV_Telemetry_Wrapper::stats()->timing('xenforo.replyNotify', microtime(true) - $starttime, 1);
        }
    }
}

if (false)
{
    class XFCP_SV_Telemetry_XenForo_Model_ThreadWatch extends XenForo_Model_ThreadWatch {}
}
