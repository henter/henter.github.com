<?php

class debug extends object
{
    static private $marker =  array(),
                   $memory_limit_on = true;

    static public function mark($name)
    {
        self::$marker['time'][$name] = microtime(true);
        if(self::$memory_limit_on)
        {
            self::$marker['mem'][$name] = memory_get_usage();
            self::$marker['peak'][$name] = function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : self::$marker['mem'][$name];
        }
    }

    static public function usetime($start, $end, $decimals = 6)
    {
        if (!isset(self::$marker['time'][$start])) return '';
        if (!isset(self::$marker['time'][$end]))
        {
            self::$marker['time'][$end] = microtime(true);
        }
        return number_format(self::$marker['time'][$end] - self::$marker['time'][$start], $decimals);
    }

    static public function usememory($start, $end)
    {
        if (!self::$memory_limit_on || !isset(self::$marker['mem'][$start])) return '';
        if (!isset(self::$marker['mem'][$end]))
        {
            self::$marker['mem'][$end] = memory_get_usage();
        }
        return number_format((self::$marker['mem'][$end] - self::$marker['mem'][$start])/1024);
    }

    static function get_peak_usage($start, $end) 
    {
        if (!self::$memory_limit_on || !isset(self::$marker['peak'][$start])) return '';
        if (!isset(self::$marker['peak'][$end]))
        {
            self::$marker['peak'][$end] = function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : memory_get_usage();
        }
        return number_format(max(self::$marker['peak'][$start], self::$marker['peak'][$end])/1024);
    }
}
?>