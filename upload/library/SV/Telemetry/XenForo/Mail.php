<?php

class SV_Telemetry_XenForo_Mail extends XFCP_SV_Telemetry_XenForo_Mail
{
    public function queue($toEmail, $toName = '', array $headers = array(), $fromEmail = '', $fromName = '', $returnPath = '')
    {
        $starttime = microtime(true);
        try
        {
            return parent::queue($toEmail, $toName, $headers, $fromEmail, $fromName, $returnPath);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.queueEmail', microtime(true) - $starttime, 1);
        }
    }
}