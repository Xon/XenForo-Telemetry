<?php

class SV_Telemetry_XenForo_Model_ThreadWatch extends XFCP_SV_Telemetry_XenForo_Model_ThreadWatch
{
    public function sendNotificationToWatchUsersOnReply(array $reply, array $thread = null, array $noAlerts = array())
    {
        $starttime = microtime(true);
        try
        {
            return parent::sendNotificationToWatchUsersOnReply($reply, $thread, $noAlerts);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.replyNotify', microtime(true) - $starttime, 1);
        }
    }
}