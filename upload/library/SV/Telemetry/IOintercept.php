<?php

include_once('SV/Telemetry/DataDog/libraries/datadogstatsd.php');

class SV_Telemetry_IOintercept
{
    public const prefix = 'xfiocount';
    public const prefix_full = 'xfiocount://';
    public const prefix_full_count = 12;

    protected static function ParsePath($path)
    {
        static $urls = array();

        if (isset($urls[$path]))
        {
            return $urls[$path];
        }

        if (substr($path, 0, self::prefix_full_count) == self::prefix_full)
        {
            $urls[$path] = substr($path, self::prefix_full_count);
            return $urls[$path];
        }
        return False;
    }

    function __construct ()
    {
    }

    function __destruct ()
    {
    }

    protected $dirhandle;

    public function dir_closedir ()
    {
        if (!isset($this->dirhandle))
        {
            return False;
        }
        @closedir($this->dirhandle);
        unset($this->dirhandle);
        return True;
    }

    public function dir_opendir($path, $options)
    {
        $path = self::ParsePath($path);
        if (!$path)
        {
            return False;
        }

        $this->dirhandle = @opendir($path);
        return $this->dirhandle !== False;
    }

    public function dir_readdir()
    {
        if (!isset($this->dirhandle))
        {
            return False;
        }
        return readdir($this->dirhandle);
    }

    public function dir_rewinddir()
    {
        if (!isset($this->dirhandle))
        {
            return False;
        }
        return rewinddir($this->dirhandle);
    }

    public function mkdir($path, $mode, $options)
    {
        $path = self::ParsePath($path);
        if (!$path)
        {
            return False;
        }
        $recursive = ($options & STREAM_MKDIR_RECURSIVE) == STREAM_MKDIR_RECURSIVE;
        $queryTime = microtime(true);
        try
        {
            return mkdir($path, $mode, $recursive);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'mkdir'));
        }
    }

    public function rmdir($path, $options)
    {
        $path = self::ParsePath($path);
        if (!$path)
        {
            return False;
        }
        $queryTime = microtime(true);
        try
        {
            return rmdir($path);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'rmdir'));
        }
    }

    protected $streamhandle;
    protected $fileRequiresUpdate = false;
    protected $parsedPath;

    public function stream_close()
    {
        $queryTime = microtime(true);
        try
        {
            $ret = fclose($this->streamhandle);
            unset($this->streamhandle);
            return $ret;
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'close'));
        }
    }

    public function stream_eof()
    {
        if(!isset($this->streamhandle))
        {
            return true;
        }
        return feof($this->streamhandle);
    }

    public function stream_flush()
    {
        if(!isset($this->streamhandle))
        {
            return false;
        }
        $queryTime = microtime(true);
        try
        {
            return fflush($this->streamhandle);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'flush'));
        }
    }

    public function stream_metadata($path, $option, $value)
    {
        $path = self::ParsePath($path);
        if (!$path)
        {
            return False;
        }
        $ret = False;
        $queryTime = microtime(true);
        try
        {
            switch($option)
            {
                case STREAM_META_TOUCH:
                    if (isset($value[0]) && isset($value[1]))
                        $ret = touch($path, $value[0], $value[1]);
                    else if (isset($value[0]))
                        $ret = touch($path, $value[0]);
                    else
                        $ret = touch($path);
                    break;
                case STREAM_META_OWNER_NAME:
                case STREAM_META_OWNER:
                    $ret = chown($path, $value);
                    break;
                case STREAM_META_GROUP_NAME:
                case STREAM_META_GROUP:
                    $ret = chgrp($path, $value);
                    break;
                case STREAM_META_ACCESS:
                    $ret = chmod($path, $value);
                    break;
                default:
                    throw new Exception("SV_Telemetry_IOintercept::stream_metadata ". $option ." not implemented");
                    break;
            }
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'metadata'));
        }
        return $ret;
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $parsedPath = self::ParsePath($path);
        if (!$parsedPath)
        {
            throw new Exception('stream_open() passed an invalid path');
        }
        $queryTime = microtime(true);
        try
        {
            $use_include_path  = ($options & STREAM_USE_PATH) == STREAM_USE_PATH;
            $this->streamhandle = fopen($parsedPath, $mode, $use_include_path);
            if ($this->streamhandle !== False)
            {
                $opened_path = $path;
            }
            else
            {
                throw new Exception('Call to fopen() failed');
            }
            return $this->streamhandle !== False;
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'open'));
        }
    }

    public function stream_read($count)
    {
        if(!isset($this->streamhandle))
        {
            return false;
        }
        $queryTime = microtime(true);
        try
        {
            return fread($this->streamhandle,$count);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'read'));
        }
    }

    public function stream_seek($offset, $whence  = SEEK_SET)
    {
        if(!isset($this->streamhandle))
        {
            return False;
        }
        return fseek($this->streamhandle, $offset, $whence) == 0;
    }

    public function stream_cast($cast_as)
    {
        return $this->streamhandle;
    }

    public function stream_stat()
    {
        if(!isset($this->streamhandle))
        {
            return false;
        }
        return fstat($this->streamhandle);
    }

    public function stream_tell()
    {
        if(!isset($this->streamhandle))
        {
            return false;
        }
        return ftell($this->streamhandle);
    }

    public function stream_truncate($new_size)
    {
        if(!isset($this->streamhandle))
        {
            return false;
        }
        $queryTime = microtime(true);
        try
        {
            return ftruncate($this->streamhandle, $new_size);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'truncate'));
        }
    }

    public function stream_write($data)
    {
        if(!isset($this->streamhandle))
        {
            return false;
        }
        $queryTime = microtime(true);
        try
        {
            return fwrite($this->streamhandle, $data);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'write'));
        }
    }

    public function unlink($path)
    {
        $path = self::ParsePath($path);
        if (!$path)
        {
            return False;
        }
        $queryTime = microtime(true);
        try
        {
            return @unlink($path);
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'unlink'));
        }
    }

    public function url_stat($path, $flags)
    {
        $path = self::ParsePath($path);
        if (!$path)
        {
            return 0;
        }
        $queryTime = microtime(true);
        try
        {
            if ($flags & STREAM_URL_STAT_LINK)
            {
                return ($flags & STREAM_URL_STAT_QUIET) ? @lstat($path) : lstat($path);
            }
            else
            {
                return ($flags & STREAM_URL_STAT_QUIET) ? @stat($path) : stat($path);
            }
        }
        finally
        {
            BatchedDatadogstatsd::timing('xenforo.io', microtime(true) - $queryTime, 1, array('io' => 'stat'));
        }
    }
}



