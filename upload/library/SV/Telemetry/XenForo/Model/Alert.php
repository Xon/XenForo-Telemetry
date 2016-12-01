<?php

class SV_Telemetry_XenForo_Model_Alert extends XFCP_SV_Telemetry_XenForo_Model_Alert
{
    public function alertUser($alertUserId, $userId, $username, $contentType, $contentId, $action, array $extraData = null)
    {
        $starttime = microtime(true);
        try
        {
            return parent::alertUser($alertUserId, $userId, $username, $contentType, $contentId, $action, $extraData);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.alertUser', microtime(true) - $starttime, 1);
        }
    }
}