<?php

class SV_Telemetry_Listener
{
    const AddonNameSpace = 'SV_Telemetry_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}