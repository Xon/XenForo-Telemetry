<?php

class SV_Telemetry_Wrapper
{
    public static function injectForIO(array &$config, $wrapperOnly = true)
    {
        if (!$wrapperOnly)
        {
            $config['internalDataPath'] = SV_Telemetry_IOintercept::prefix_full . (isset($config['internalDataPath']) ? $config['internalDataPath'] : 'internal_data');
            $config['externalDataPath'] = SV_Telemetry_IOintercept::prefix_full . (isset($config['externalDataPath']) ? $config['externalDataPath'] : 'data');
            stream_wrapper_register(SV_Telemetry_IOintercept::prefix, "SV_Telemetry_IOintercept");
        }

        if (class_exists('SV_Csync2StreamWrapper_CsyncConfig', true))
        {
            SV_Telemetry_CsyncIOintercept::setup();
        }
    }

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
        if (isset($config['cache']['backend']))
        {
            self::injectForCacheArgs($config['cache']);
        }
        if (isset($config['sessionCache']['backend']))
        {
            self::injectForCacheArgs($config['sessionCache']);
        }
    }

    protected static function injectForCacheArgs(array &$cache)
    {
        if (empty($cache['backend']) || !is_string($cache['backend']))
        {
            return;
        }

        $backendClass = 'Zend_Cache_Backend_' . $cache['backend'];
        // glue the telemetry adaptor to the previously configured backend
        if (!class_exists('XFCP_SV_Telemetry_Cacheintercept', false))
        {
            eval('class XFCP_SV_Telemetry_Cacheintercept extends '.$backendClass.' { }');
        }
        // only support intercepting multiple cache class usages if they have the same root
        else if (get_parent_class('XFCP_SV_Telemetry_Cacheintercept') != $backendClass)
        {
            return;
        }

        // due to poor extensibiliy, Cacheintercept actually resolves to Zend_Cache_Backend_Cacheintercept.
        // this class just includes SV_Telemetry_Cacheintercept
        $cache['backend'] = 'Cacheintercept';
    }
}