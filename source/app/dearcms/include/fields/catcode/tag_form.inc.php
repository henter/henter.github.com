	function catcode($field, $value, $fieldinfo)
	{
		global $CAT;
		extract($fieldinfo);
		$js = "<script type=\"text/javascript\">
					function category_load(id)
					{
						\$.get('load.php', { field: 'catcode', id: id },
							  function(data){
								\$('#load_$field').append(data);
							  });
					}
					function category_reload()
					{
						\$('#load_$field').html('');
						category_load(0);
					}
					category_load(0);
			</script>";
		if($value)
		{
			$catname = $CAT[$value]['catname'];
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"10\">
			<span onclick=\"this.style.display='none';\$('#reselect_$field').show();\" style=\"cursor:pointer;\">$catname <font color=\"red\">点击重选</font></span>
			<span id=\"reselect_$field\" style=\"display:none;\">
			<span id=\"load_$field\"></span> 
			<a href=\"javascript:category_reload();\">重选</a>
			</span>$js";
		}
		else
		{
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"10\">
			<span id=\"load_$field\"></span>
			<a href=\"javascript:category_reload();\">重选</a>$js";
		}
    }
