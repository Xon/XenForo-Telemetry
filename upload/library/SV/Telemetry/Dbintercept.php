<?php

class SV_Telemetry_Dbintercept extends XFCP_SV_Telemetry_Dbintercept
{
    protected $transactionTime = 0;

    public function query($sql, $bind = [])
    {
        //$type
        $queryTime = microtime(true);
        try
        {
            return parent::query($sql, $bind);
        }
        finally
        {
            SV_Telemetry_Wrapper::stats()->timing('xenforo.db.query', microtime(true) - $queryTime, 1);//, array('tagname' => $type));
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
            SV_Telemetry_Wrapper::stats()->timing('xenforo.db.transaction', microtime(true) - $this->transactionTime, 1, ['transtype' => 'commit']);
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
            SV_Telemetry_Wrapper::stats()->timing('xenforo.db.transaction', microtime(true) - $this->transactionTime, 1, ['transtype' => 'rollback']);
            $this->transactionTime = 0;
        }
    }
}

if (false)
{
    class XFCP_SV_Telemetry_Dbintercept extends Zend_Db_Adapter_Mysqli {}
}
