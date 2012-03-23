	function image($field, $value, $fieldinfo)
	{
		global $catcode,$DC;
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$getimg = $get_img ? '<input type="checkbox" name="info[getpictothumb]" value="1" checked /> 保存文章第一张图片为缩略图' : '';
		if(defined('IN_ADMIN'))
		{
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"$size\" class=\"$css\" $formattribute/> <input type=\"hidden\" name=\"{$field}_aid\" id=\"{$field}_aid\" value=\"0\"> <input type=\"button\" name=\"{$field}_upimage\" id=\"{$field}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('?file=upload_field&uploadtext={$field}&modelid={$modelid}&catcode={$catcode}&fieldid={$fieldid}','upload','450','350')\"/> {$getimg}";
		}
		else
		{
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"$size\" class=\"$css\" $formattribute/> <input type=\"hidden\" name=\"{$field}_aid\" id=\"{$field}_aid\" value=\"0\"> <input type=\"button\" name=\"{$field}_upimage\" id=\"{$field}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('".DC_PATH."upload_field.php?uploadtext={$field}&modelid={$modelid}&catcode={$catcode}&fieldid={$fieldid}','upload','450','350')\"/> ";
		}
	}
