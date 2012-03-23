	function pages($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if($value)
		{
			$v = explode('|', $value);
			$data = '<select name="info[paginationtype]" id="paginationtype" onchange="if(this.value==1)paginationtype1.style.display=\'\'; else paginationtype1.style.display=\'none\';">';
			$type = array('不分页', '自动分页', '手动分页');
			if($v[0]==1) $con = 'style="display:"';
			else $con = 'style="display:none"';
			foreach($type as $i => $val)
			{
				if($i==$v[0]) $tag = 'selected';
				else $tag = '';
				$data .= "<option value=\"$i\" $tag>$val</option>";
			}
			$data .= "</select> &nbsp;&nbsp;&nbsp;&nbsp;<strong><font color=\"#0000FF\">注：</font></strong><font color=\"#0000FF\">手动分页时，将光标放在需要分页处，点编辑器下面的“</font> 分页 <font color=\"#0000FF\">”即可。点击“</font> 子标题 <font color=\"#0000FF\">”可以设置每篇分页的标题。</font><div id=\"paginationtype1\" $con>自动分页时的每页大约字符数（包含HTML标记）<strong> <input name=\"info[maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"$v[1]\" size=\"8\" maxlength=\"8\"></strong></div>";
			return $data;
		}
		else
		{
			return "<select name=\"info[paginationtype]\" id=\"paginationtype\" onchange=\"if(this.value==1)paginationtype1.style.display=''; else paginationtype1.style.display='none';\">
                <option value=\"0\">不分页</option>
                <option value=\"1\">自动分页</option>
                <option value=\"2\">手动分页</option>
            </select> &nbsp;&nbsp;&nbsp;&nbsp;<strong><font color=\"#0000FF\">注：</font></strong><font color=\"#0000FF\">手动分页时，将光标放在需要分页处，点编辑器下面的“</font> 分页 <font color=\"#0000FF\">”即可。点击“</font> 子标题 <font color=\"#0000FF\">”可以设置每篇分页的标题。</font>
			<div id=\"paginationtype1\" style=\"display:none\">自动分页时的每页大约字符数（包含HTML标记）<strong> <input name=\"info[maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"10000\" size=\"8\" maxlength=\"8\"></strong></div>";
		}
	}
