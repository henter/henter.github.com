	function title($field, $value, $fieldinfo)
	{
		global $catcode;
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		global $catcode;
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$data = '';
		if(defined('IN_ADMIN'))
		{
			$data = "<input type=\"button\" value=\"检测标题是否已存在\" onclick=\"$.post('?file=content&catcode=".$catcode."', { action : 'check_title', c_title:$('#title').val()}, function(data){ $('#t_msg').html(data); })\">&nbsp;<span style=\"color:'#ff0000'\" id='t_msg'></span>";
		}
		$formattribute .= 'onBlur="$.post(\'api/get_keywords.php?number=3&sid=\'+Math.random()*5, {data:$(\'#title\').val()}, function(data){if(data) $(\'#keywords\').val(data); })"';
		return form::text('info['.$field.']', $field, $value, 'text', $size, $css, $formattribute, $minlength, $maxlength).$data;
	}
