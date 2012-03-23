<?php

class segment
{
	private $scws;

	function __construct()
	{
		if (extension_loaded('scws'))
		{
			$this->scws = scws_new();
		}
		elseif (file_exists(FW_PATH.'helper'.DS.'pscws4.php'))
		{
			import('helper.pscws4');
			define('RESOURCE_PATH', ROOT_PATH.DS.'cmstop'.DS.'resources'.DS.'scws'.DS);
			$this->scws = new PSCWS4('utf8');
			$this->scws->set_dict(RESOURCE_PATH.'dict.utf8.xdb');
			$this->scws->set_rule(RESOURCE_PATH.'rules.ini');
		}
	}

	function set_charset($charset = 'gbk')
	{
		return $this->scws->set_charset($charset);
	}

	function set_dict($path)
	{
		return $this->scws->set_dict($path);
	}

	function set_rule($path)
	{
		return $this->scws->set_rule($path);
	}

	function set_ignore($isignore = true)
	{
		return $this->scws->set_ignore($isignore);
	}

	function set_multi($ismulti = true)
	{
		return $this->scws->set_multi($ismulti);
	}

	function set($dict = '', $rule = '', $charset = 'gbk', $isignore = true, $ismulti = true)
	{
		if($dict) $this->set_dict($dict);
		if($rule) $this->set_rule($rule);
		if($charset) $this->set_charset($charset);
		$this->set_ignore($isignore);
		$this->set_multi($ismulti);
	}

	function set_text($text)
	{
		return $this->scws->send_text(strip_tags($text));
	}

	function get_result()
	{
		return $this->scws->get_result();
	}

	function get_tops($limit = 10, $attr = NULL)
	{
		return $this->scws->get_tops($limit, $attr);
	}

	function get_words($attr = 'c,e,f,o,p,u,w,y,uj')
	{
		$this->set_ignore(true);
		$this->set_multi(true);
		if($attr) $attr = explode(',', $attr);
		$words = '';
		while($r = $this->scws->get_result())
		{
			foreach($r as $v)
			{
				if(($attr && in_array($v['attr'], $attr)) || trim($v['word']) == '') continue;
				$words .= ' '.$v['word'];
			}
		}
		return trim($words);
	}

	function get_keywords($number = 3)
	{
		$this->set_multi(false);
		$words = array();
		$array = $this->get_tops($number, $attr);
		if(!$array) return '';
		foreach($array as $r)
		{
			$words[] = trim($r['word']);
		}
		return $words;
	}

	function close()
	{
		return $this->scws->close();
	}

	function version()
	{
		return $this->scws->version();
	}
}