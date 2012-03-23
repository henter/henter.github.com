<?php

class form_element
{
	private static $enctype = 'application/x-www-form-urlencoded';
	private $elements;
	
	function __construct()
	{
		
	}

	static public function &get_instance()
	{
		static $instance;
		if (!is_null($instance)) return $instance;
		$instance = new form_element();
		return $instance;
	}
	
	static function text($settings = array())
	{
        $settings['type'] = 'text';
        return self::_input($settings);
	}
	
	static function password($settings = array())
	{
        $settings['type'] = 'password';
        return self::_input($settings);
	}

	static function hidden($settings = array())
	{
        $settings['type'] = 'hidden';
        return self::_input($settings);
	}

	static function textarea($settings = array())
	{
		if(!is_array($settings) || !isset($settings['name'])) return FALSE;
		extract($settings);
		$html  = "<textarea name=\"$name\" id=\"$id\" ";
		if(isset($rows)) $html .= " rows=\"$rows\" ";
		if(isset($cols)) $html .= " cols=\"$cols\" ";
		if(isset($wrap)) $html .= " wrap=\"$wrap\" ";
        $html .= self::_attribute($settings);
		$html .= " >";
		if(isset($value)) $html .= htmlspecialchars($value);
		$html .= "</textarea>";
        return $html;
	}

	static function select($settings = array())
	{
		if(!is_array($settings) || !isset($settings['name'])) return FALSE;
		extract($settings);
		if (!isset($id)) $id = $name;
		$html  = "<select name=\"$name\" id=\"$id\" ";
		if(isset($size)) $html .= " size=\"$size\" ";
		if(isset($multiple)) $html .= " multiple=\"multiple\" ";
        $html .= self::_attribute($settings);
		$html .= " >\n";
		foreach ($options as $k=>$v)
		{
			$selected = $k == $value ? 'selected="selected"' : '';
			$html .= "<option $selected value=\"$k\">$v</option>\n";
		}
		$html .= "</select>";
        return $html;
	}

	static function radio($settings = array())
	{
        $settings['type'] = 'radio';
        return self::_check($settings);
	}
	
	static function checkbox($settings = array())
	{
        $settings['type'] = 'checkbox';
        return self::_check($settings);
	}

	static function file($settings = array())
	{
		if(!is_array($settings) || !isset($settings['name'])) return FALSE;
		extract($settings);
		if (!isset($id)) $id = $name;
		$html = '';
		if(isset($max_file_size)) $html .= "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_file_size\" />";
		$attr = self::_attribute($settings);
		$size = isset($size) ? " size=\"$size\" " : '';
		$html .= "<input type=\"file\" name=\"$name\" id=\"$id\" $size $attr />";
		self::$enctype = 'multipart/form-data';
		return $html;
	}
	
	static function button($settings = array())
	{
        $settings['type'] = 'button';
        return self::_button($settings);
	}

	static function submit($settings = array())
	{
        $settings['type'] = 'submit';
        return self::_button($settings);
	}

	static function reset($settings = array())
	{
        $settings['type'] = 'reset';
        return self::_button($settings);
	}
	
	static function image($settings = array())
	{
		if(!is_array($settings) || !isset($settings['name']) || !isset($settings['src'])) return FALSE;
		extract($settings);
		if (!isset($id)) $id = $name;
		$attr = self::_attribute($settings);
		$html = "<input type=\"image\" name=\"$name\" id=\"$id\" src=\"$src\"";
		if(isset($align)) $html .= " align=\"$align\" ";
		if(isset($attr)) $html .= $attr;
		$html .= '/>';
		return $html;
	}
	
	private static function _input($settings = array())
	{
		if(!is_array($settings) || !isset($settings['name']) || !isset($settings['type'])) return FALSE;
		extract($settings);
		if (!isset($id)) $id = $name;
		$html  = "<input type=\"$type\" name=\"$name\" id=\"$id\"  style=\"$style\" ";
		if(isset($value)) $html .= " value=\"".htmlspecialchars($value)."\" ";
		if(isset($size)) $html .= " size=\"$size\" ";
		if(isset($maxlength)) $html .= " maxlength=\"$maxlength\" ";
        $html .= self::_attribute($settings);
		$html .= ' />';
		return $html;
	}

	private static function _check($settings = array())
	{
		if(!is_array($settings) || !isset($settings['name']) || !isset($settings['type'])) return FALSE;
		extract($settings);
		$attr = self::_attribute($settings);
		$html = '';
		$i = 1;
		foreach ($options as $v=>$label)
		{
			$checked = $v == $value ? ' checked="checked"' : '';
		    $html .= "<label><input type=\"$type\" name=\"$name\" id=\"{$name}_{$i}\" value=\"$v\" $checked $attr /> $label </label>\n";
		    if (isset($br)) $html .= '<br />';
		    $i++;
		}
        return $html;
	}

	private static function _button($settings = array())
	{
		if(!is_array($settings) || !isset($settings['name']) || !isset($settings['value'])) return FALSE;
		extract($settings);
		if (!isset($id)) $id = $name;
		$attr = self::_attribute($settings);
		$html = "<input type=\"$type\" name=\"$name\" id=\"$id\" value=\"$value\" $attr />";
		return $html;
	}
	
	private static function _attribute($settings = array())
	{
		extract($settings);
		$html = '';
		if(isset($readonly)) $html .= " readonly";
		if(isset($disabled)) $html .= " disabled";
		if(isset($alt)) $html .= " alt=\"$alt\" ";
		if(isset($tabindex)) $html .= " tabindex=\"$tabindex\" ";
		if(isset($accesskey)) $html .= " accesskey=\"$accesskey\" ";
		if(isset($class)) $html .= " class=\"$class\" ";
		if(isset($attribute)) $html .= " $attribute ";
		return $html;
	}
}