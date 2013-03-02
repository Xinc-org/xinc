<?php
class Xinc_StreamLogger
{
    public $position;
    public $varname;
    
    private static $_logItems = array();
  
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url["host"];
        $this->position = 0;
       
        return true;
    }

    function stream_read($count)
    {
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_write($data)
    {
        self::$_logItems[] = $data;
        //echo $data;
        return strlen($data);
    }
    function url_stat($data)
    {
        return array("mode"=>0777);
    }
    function stream_tell()
    {
        return $this->position;
    }

    function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    }

    function stream_seek($offset, $whence)
    {
        return true;
    }
    
    public static function getLastLogMessage()
    {
        return self::$_logItems[count(self::$_logItems)-1];
    }
    
    public static function getLogMessageFromEnd($no)
    {
        return self::$_logItems[count(self::$_logItems)-($no+1)];
    }
}

