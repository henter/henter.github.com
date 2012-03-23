	function areacode($field, $value, $fieldinfo)
	{
		global $AREA;
		extract($fieldinfo);
		$js = "<script type=\"text/javascript\">
					function area_load(id)
					{
						\$.get('load.php', { field: 'areacode', id: id },
							  function(data){
								\$('#load_$field').append(data);
							  });
					}
					function area_reload()
					{
						\$('#load_$field').html('');
						area_load(0);
					}
					area_load(0);
			</script>";
		if($value)
		{
			$areaname = $AREA[$value]['name'];
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"10\">
			<span onclick=\"this.style.display='none';\$('reselect_$field').style.display='';\" style=\"cursor:pointer;\">$areaname <font color=\"red\">点击重选</font></span>
			<span id=\"reselect_$field\" style=\"display:none;\">
			<span id=\"load_$field\"></span> 
			<a href=\"javascript:area_reload();\">重选</a>
			</span>$js";
		}
		else
		{
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"10\">
			<span id=\"load_$field\"></span>
			<a href=\"javascript:area_reload();\">重选</a>$js";
		}
	}
