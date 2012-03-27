<?php
abstract class resource
{
	protected static $_package = array();
	protected static $_needs = array();
	protected static $_adapter = array();
	
	public static function init()
	{
		self::$_package = include (CMSTOP_PATH.'resources/depends.db');
		self::setAdapter('css', array(self, '_genLink'));
		self::setAdapter('js', array(self, '_genScript'));
		self::$_needs = array();
	}
	protected static function _genLink($val)
	{
		return '<link rel="stylesheet" type="text/css" href="'.$val.'" />';
	}
	protected static function _genScript($val)
	{
		return '<script type="text/javascript" src="'.$val.'"></script>';
	}
	
	protected static function _depends($depends)
	{
		foreach ($depends as $key)
		{
			if (array_key_exists($key, self::$_package))
			{
				$val = self::$_package[$key];
				if ($val['depends'])
				{
					self::_depends((array) $val['depends']);
				}
				$base = isset($val['base']) ? (rtrim($val['base'], '/') . '/') : '';
				foreach ((array) $val['resource'] as $v)
				{
					if (preg_match('|^http[s]?\://|i', $v))
					{
						self::$_needs[] = $v;
					}
					else
					{
						self::$_needs[] = $base . $v;
					}
				}
			}
		}
	}
	public static function depends($items)
	{
		self::_depends((array) $items);
		return self::$_needs = array_unique(self::$_needs);
	}
	public static function needs($items)
	{
		return self::depends($items);
	}
	public static function import($name, $data = null)
	{
		if (is_array($name))
		{
			self::$_package = array_merge(self::$_package, $name);
		}
		else
		{
			self::$_package[$name] = $data;
		}
	}
	public static function addResource($name, $data = null)
	{
		return self::import($name, $data);
	}
	
	public static function toHtml()
	{
		$html = array();
		foreach (self::$_needs as $item)
		{
			$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
			if (array_key_exists($ext, self::$_adapter))
			{
				$html[] = call_user_func(self::$_adapter[$ext], $item);
			}
		}
		return implode('', $html);
	}
	public static function setAdapter($name, $adapter)
	{
		self::$_adapter[$name] = $adapter;
	}
	public static function pack()
	{
		
	}
}