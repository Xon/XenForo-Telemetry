<?php

class SV_Telemetry_CsyncIOintercept extends SV_Csync2StreamWrapper_CsyncConfig
{
    public static function setup()
    {
        $csync2Config = SV_Csync2StreamWrapper_CsyncConfig::getInstance();
        // hijack the instance variable so we can override the csync injection logic
        self::$_instance = new self();
        self::$_instance->setInstalled($csync2Config->isInstalled());
    }

    public function RegisterStream()
    {
        if ($this->isInstalled())
        {
            return;
        }
        $this->setInstalled(true);

        stream_wrapper_register(SV_Csync2StreamWrapper_csyncwrapper::prefix, "SV_Csync2StreamWrapper_csyncwrapper");
    }

    public function pushSingeChange($path)
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

    public function pushBulkChanges($flags)
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