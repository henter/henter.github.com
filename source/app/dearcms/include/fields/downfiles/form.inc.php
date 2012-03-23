	function downfiles($field, $value, $fieldinfo)
	{
		global $catcode;
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$string = "<textarea name='info[$field]' cols='70' rows='6' id='$field' $formattribute>".$value."</textarea><input type='hidden' name='".$field."_aid' value='' id='".$field."_aid'>";
		if(defined('IN_ADMIN'))
		{
			$string .= "<iframe id='uploads' name='uploads' src='?file=downfiles&uploadtext={$id}&catcode={$catcode}' border='0' vspace='0' hspace='0' marginwidth='0' marginheight='0' framespacing='0' frameborder='0' scrolling='no' width='100%' height='50'></iframe>";
		}
		else
		{
			$string .= "<iframe id='uploads' name='uploads' src='downfiles.php?uploadtext={$id}&catcode={$catcode}' border='0' vspace='0' hspace='0' marginwidth='0' marginheight='0' framespacing='0' frameborder='0' scrolling='no' width='100%' height='50'></iframe>";
		}
		return $string;
	}
