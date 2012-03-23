	function areacode($field, $value, $fieldinfo)
	{
		global $AREA,$areacode;
              if($areacode) $value = $areacode;
		extract($fieldinfo);
		$js = "<script type=\"text/javascript\">
					function area_load(id)
					{
						$.get('load.php', { field: 'areacode', id: id, value: '".$field."' },
							  function(data){
								$('#load_$field').append(data);
							  });
					}
					function area_reload()
					{
						$('#load_$field').html('');
						area_load(0);
					}
					area_load(0);
			</script>";
		if($value)
		{
                    return "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\">".areaname($value,1);
                    //去掉 不允许修改信息地址 因为是跟商铺对应的
			//return "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\"><span onclick=\"this.style.display='none';\$('#reselect_$field').show();\" style=\"cursor:pointer;\">  ".areaname($value,1)."  <font color=\"red\">点击重选</font></span><span id=\"reselect_$field\" style=\"display:none;\"><span id=\"load_$field\"></span> <a href=\"javascript:area_reload();\">重选</a></span>$js";
		}
		else
		{
			return "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\">
			<span id=\"load_$field\"></span>
			<a href=\"javascript:area_reload();\">重选</a>$js";
		}
	}
