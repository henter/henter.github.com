	function keyword($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$infos = cache_read('keyword.php');
		$data = '<select name="select_keyword" onchange="if($(\'#'.$field.'\').val()){$(\'#'.$field.'\').val(this.value);}else{$(\'#'.$field.'\').val(this.value);}" style="width:85px"><option>常用关键词</option>';
		foreach($infos as $info)
		{
			$data .= "<option value='{$info}'>{$info}</option>\n";
		}
		$data .= '</select> <a href="###" onclick="SelectKeyword();">更多&gt;&gt;</a>';
		return form::text('info['.$field.']', $field, $value, $type, 20, $css, $formattribute).$data;
	}
