<?php

// This class is used to encapsulate global state between layers without using $GLOBAL[] or
// relying on the consumer being loaded correctly by the dynamic class autoloader
class SV_Telemetry_Globals
{
    public static function addTiming($type, $id, $contentIds, $starttime, $endtime)
    {
        $data = json_encode(array($type, $id, $contentIds, $starttime, $endtime));
        XenForo_Error::debug($type .' '. $id . ' '.var_export($contentIds, true) . ' ' .($endtime - $starttime). 's');
    }

    private function __construct() {}
}