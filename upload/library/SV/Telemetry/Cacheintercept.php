<?php

include_once('SV/Telemetry/DataDog/libraries/datadogstatsd.php');

class SV_Telemetry_Cacheintercept extends XFCP_SV_Telemetry_Cacheintercept
{
    public function load($id, $doNotTestCacheValidity = false)
    {
        $queryTime = microtime(true);
        try
        {
            return parent::load($id, $doNotTestCacheValidity);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.cache', microtime(true) - $queryTime, 1, array('operation' => 'load'));
        }
    }

    public function test($id)
    {
        $queryTime = microtime(true);
        try
        {
            return parent::test($id);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.cache', microtime(true) - $queryTime, 1, array('operation' => 'test'));
        }
    }
    
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $queryTime = microtime(true);
        try
        {
            return parent::save($data, $id, $tags, $specificLifetime);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.cache', microtime(true) - $queryTime, 1, array('operation' => 'save'));
        }
    }
    
    public function remove($id)
    {
        $queryTime = microtime(true);
        try
        {
            return parent::remove($id);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.cache', microtime(true) - $queryTime, 1, array('operation' => 'remove'));
        }
    }
}