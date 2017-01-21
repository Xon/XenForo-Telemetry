<?php

class SV_Telemetry_CsyncIOintercept extends SV_Csync2StreamWrapper_csyncwrapper
{
    public static function pushSingeChange($path)
    {
        $queryTime = microtime(true);
        try
        {
            parent::pushSingeChange($path);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'csync2'));
        }
    }

    public static function pushBulkChanges($flags)
    {
        $queryTime = microtime(true);
        try
        {
            parent::pushBulkChanges($flags);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'csync2'));
        }
    }
}