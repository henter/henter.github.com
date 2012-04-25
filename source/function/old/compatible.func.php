<?php
/*
    兼容函数
*/

if(!function_exists('fputcsv'))
{
	function fputcsv(&$fp, $array, $delimiter = ',', $enclosure = '"')
	{
		$data = $enclosure.implode($enclosure.$delimiter.$enclosure, $array).$enclosure."\n";
		return fwrite($fp, $data);
	}
}


if(!function_exists('file_put_contents'))
{
	define('FILE_APPEND', 8);
	function file_put_contents($file, $data, $append = '')
	{
		$mode = $append == '' ? 'wb' : 'ab';
		$fp = @fopen($file, $mode) or exit("Can not open file $file !");
		flock($fp, LOCK_EX);
		$len = @fwrite($fp, $data);
		flock($fp, LOCK_UN);
		@fclose($fp);
		return $len;
	}
}

if(!function_exists('http_build_query'))
{
    function http_build_query($data, $prefix = null, $sep = '', $key = '')
	{
        $ret = array();
		foreach((array)$data as $k => $v)
		{
			$k = urlencode($k);
			if(is_int($k) && $prefix != null)
			{
				$k = $prefix.$k;
			}
			if(!empty($key)) {
				$k = $key."[".$k."]";
			}
			if(is_array($v) || is_object($v))
			{
				array_push($ret,http_build_query($v,"",$sep,$k));
			}
			else
			{
				array_push($ret,$k."=".urlencode($v));
			}
		}
        if(empty($sep))
		{
            $sep = ini_get("arg_separator.output");
        }
        return implode($sep, $ret);
    }
}

if(!function_exists('image_type_to_extension'))
{
    function image_type_to_extension($type, $dot = true)
    {
        $e = array ( 1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp' ,'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc', 'aiff', 'wbmp', 'xbm');
        $type = intval($type);
        if (!$type)
		{
            trigger_error( 'File Type is null...', E_USER_NOTICE );
            return null;
        }
        if(!isset($e[$type]))
		{
            trigger_error( 'Image type is wrong...', E_USER_NOTICE );
            return null;
        }
        return ($dot ? '.' : '') . $e[$type];
    }
}

if(!function_exists('array_intersect_key'))
{
	function array_intersect_key($isec, $keys)
	{
		$argc = func_num_args();
		if ($argc > 2)
		{
			for ($i = 1; !empty($isec) && $i < $argc; $i++)
			{
				$arr = func_get_arg($i);
				foreach (array_keys($isec) as $key)
				{
					if (!isset($arr[$key]))
					{
						unset($isec[$key]);
					}
				}
			}
			return $isec;
		}
		else
		{
			$res = array();
			foreach (array_keys($isec) as $key)
			{
				if (isset($keys[$key]))
				{
					$res[$key] = $isec[$key];
				}
			}
			return $res;
		}
	}
}

if(!function_exists('json_encode'))
{
	function json_encode($string)
	{
		require_once 'json.class.php';
		$json = new json();
		return $json->encode($string);
	}
}

if(!function_exists('json_decode'))
{
	function json_decode($string,$type = 1)
	{
		require_once 'json.class.php';
		$json = new json();
		return $json->decode($string,$type);
	}
}

if(!function_exists('iconv'))
{
	function iconv($in_charset, $out_charset, $str)
	{
		if(function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($str, $out_charset, $in_charset);
		}
		else
		{

			require_once 'iconv.func.php';
			$in_charset = strtoupper($in_charset);
			$out_charset = strtoupper($out_charset);
			if($in_charset == 'UTF-8' && ($out_charset == 'GBK' || $out_charset == 'GB2312'))
			{
				return utf8_to_gbk($str);
			}
			if(($in_charset == 'GBK' || $in_charset == 'GB2312') && $out_charset == 'UTF-8')
			{
				return gbk_to_utf8($str);
			}
			return $str;
		}
	}
}
