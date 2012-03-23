	function catcode($field, $value, $fieldinfo)
	{
		global $CAT,$mod;
        if(defined('IN_CP')){
            global $file;
            if($file=='content') $mod='dearcms';
            if($file=='shop') $mod='shop';
        } 
              $mod = $mod ? $mod : 'dearcms';
		extract($fieldinfo);
		$js = "<script type=\"text/javascript\">
					function cat_load(id)
					{
						$.get('load.php', { field: 'catcode', id: id, value: '".$field."' , mod: '".$mod."' },
							  function(data){
								$('#load_$field').append(data);
							  });
					}
					function cat_reload()
					{
						$('#load_$field').html('');
						cat_load(0);
					}
					cat_load(0);
			</script>";
		if($value)
		{
			return "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\">
			<span onclick=\"this.style.display='none';\$('#reselect_$field').show();\" style=\"cursor:pointer;\">  ".catname($value,1)."  <font color=\"red\">点击重选</font></span>
			<span id=\"reselect_$field\" style=\"display:none;\">
			<span id=\"load_$field\"></span> 
			<a href=\"javascript:cat_reload();\">重选</a>
			</span>$js";
		}
		else
		{
			return "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\">
			<span id=\"load_$field\"></span>
			<a href=\"javascript:cat_reload();\">重选</a>$js";
		}
	}
