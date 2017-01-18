<?php

class SV_Telemetry_Wrapper
{
    public static function injectForDatabase(array &$config)
    {

        $adapter = isset($config['db']['adapter']) ? $config['db']['adapter'] : 'mysqli';
        $adapterNamespace = isset($config['db']['adapterNamespace']) ? $config['db']['adapterNamespace'] : 'Zend_Db_Adapter';

        // Adapter no longer normalized- see http://framework.zend.com/issues/browse/ZF-5606
        $adapterName = $adapterNamespace . '_';
        $adapterName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));
        // glue the telemetry adaptor to the previously configured
        eval("class XFCP_SV_Telemetry_Dbintercept extends $adapterName {}");
        
        $config['db']['adapter'] = 'Dbintercept';
        $config['db']['adapterNamespace'] = 'SV_Telemetry';
    }
    
    public static function injectForCache(array &$config)
    {
    }
}