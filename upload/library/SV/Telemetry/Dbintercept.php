<?php

include_once('SV/Telemetry/DataDog/libraries/datadogstatsd.php');

class SV_Telemetry_Dbintercept extends XFCP_SV_Telemetry_Dbintercept
{
    protected $transactionTime = 0;

    public function query($sql, $bind = array())
    {
        //$type
        $queryTime = microtime(true);
        try
        {
            return parent::query($sql, $bind);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.db.query', microtime(true) - $queryTime, 1);//, array('tagname' => $type));
        }
    }

    public function beginTransaction()
    {
        $this->transactionTime = microtime(true);
        return parent::beginTransaction();
    }

    public function commit()
    {
        try
        {
            return parent::commit();
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.db.transaction', microtime(true) - $this->transactionTime, 1, array('tagname' => 'commit'));
            $this->transactionTime = 0;
        }
    }

    public function rollBack()
    {
        try
        {
            return parent::rollBack();
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.db.transaction', microtime(true) - $this->transactionTime, 1, array('tagname' => 'rollback'));
            $this->transactionTime = 0;
        }
    }
}