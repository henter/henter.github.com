<?php
class form
{

	function editor($textareaid = 'content', $toolbar = 'standard', $width = '100%', $height = 350, $isshowext = 1)
	{
            global $APP, $mod, $file, $_userid;
		if(!defined('KE_INIT'))
		{
			define('KE_INIT', 1);
                    $str .= '<script type="text/javascript" charset="utf-8" src="inc/ke/kindeditor.js"></script>';
		}
            if($toobar != 'standard'){
                $item = "items : ['source', 'fullscreen', 'fontname', 'fontsize', 'textcolor', 'bgcolor', 'bold', 'italic', 'underline','removeformat', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist','insertunorderedlist', 'emoticons', 'image', 'link','advtable']";
            }else{
                $item = "items : ['source', 'fullscreen', 'undo', 'redo', 'print', 'cut', 'copy', 'paste','plainpaste', 'wordpaste', 'justifyleft', 'justifycenter', 'justifyright','justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript','superscript', 'selectall', '-','title', 'fontname', 'fontsize', 'textcolor', 'bgcolor', 'bold','italic', 'underline', 'strikethrough', 'removeformat', 'image','flash', 'media', 'table', 'hr', 'emoticons', 'link', 'unlink', 'about']";
            }

            
            $str .= "<script type=\"text/javascript\">
                var Module = '".$mod."';
    KE.show({
        id : '".$textareaid."',
	imageUploadJson : '".SITE_URL."attachment.php?action=upload&module=' + Module + '&from=keditor&dosubmit=1',
        width : '".$width."',
        height : '".$height."',
        minwidth : '200',
        minheight : '200',
	allowFileManager : true,
        cssPath : './index.css',
	resizeMode : 0,
        urlType : 'absolute',
        wyswygMode : true,
	afterCreate : function(id) {if(typeof(ke_competed)=='function') {ke_competed(id);} },

        ".$item ."
    });
  </script>
      <style>
      /*避免出现编辑器样式问题 由admin.css 189-196影响*/
.ke-container th {
	border-bottom: 0;
}
.ke-container td {
	border-bottom: 0;
	padding: 0px;
	line-height: 15px;
}
      </style>
            ";

		return $str;
	}

	function editor2($textareaid = 'content', $toolbar = 'standard', $width = '100%', $height = 400, $isshowext = 1)
	{
	    global $DC, $mod, $file, $_userid;
		$str = "<script type=\"text/javascript\" src=\"fckeditor/fckeditor.js\"></script>\n<script language=\"JavaScript\" type=\"text/JavaScript\">var SiteUrl = \"".SITE_URL."\"; var Module = \"".$mod."\"; var sBasePath = \"".SITE_URL."\" + 'fckeditor/'; var oFCKeditor = new FCKeditor( '".$textareaid."' ) ; oFCKeditor.BasePath = sBasePath ; oFCKeditor.Height = '".$height."'; oFCKeditor.Width	= '".$width."' ; oFCKeditor.ToolbarSet	= '".$toolbar."' ;oFCKeditor.ReplaceTextarea();";
		$str .= "</script>";
		if($isshowext)
		{
			$str .= "<div style='width:$width;text-align:left'>";
			$str .= "<img src=\"".SITE_URL."images/editor_add.jpg\" title='增加编辑器高度' tag='1' fck=\"".$textareaid."\"/>&nbsp;  <img src=\"".SITE_URL."images/editor_diff.jpg\" title='减少编辑器高度' tag='0' fck=\"".$textareaid."\"/></div>";
		}
		return $str;
	}

	function date($name, $value = '', $isdatetime = 0)
	{
		if($value == '0000-00-00 00:00:00') $value = '';
		$id = preg_match("/\[(.*)\]/", $name, $m) ? $m[1] : $name;
		if($isdatetime)
		{
			$size = 21;
			$format = 'yyyy-MM-dd HH:mm:ss';
		}
		else
		{
			$size = 10;
			$format = 'yyyy-MM-dd';
		}
		$str = '';
		if(!defined('CALENDAR_INIT'))
		{
			define('CALENDAR_INIT', 1);
			//$str .= '<script type="text/javascript" defer="defer"  src="images/js/My97DatePicker/WdatePicker.js"></script>';
			$str .= '<script type="text/javascript" defer="defer"  src="'.SITE_URL.'images/js/My97DatePicker/WdatePicker.js"></script>';
		}
		$str .= '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" size="'.$size.'" readonly onfocus="WdatePicker({dateFmt:\''.$format.'\'});" />&nbsp;';

		return $str;
	}

	function checkcode($name = 'checkcode', $size = 4, $extra = '')
	{
		return '<input name="'.$name.'" id="'.$name.'" type="text" size="'.$size.'" '.$extra.' style="ime-mode:disabled;"> <img src="'.SITE_URL.'checkcode.php" id="checkcode" onclick="this.src=\''.SITE_URL.'checkcode.php?id=\'+Math.random()*5;" style="cursor:pointer;" alt="验证码,看不清楚?请点击刷新验证码" align="absmiddle"/>';
	}

	function style($name = 'style', $style = '')
	{
		global $styleid, $LANG;
		if(!$styleid) $styleid = 1; else $styleid++;
		$color = $strong = '';
		if($style)
		{
			list($color, $b) = explode(' ', $style);
		}
		$styleform = "<option value=\"\">".$LANG['color']."</option>\n";
		for($i=1; $i<=15; $i++)
		{
			$styleform .= "<option value=\"c".$i."\" ".($color == 'c'.$i ? "selected=\"selected\"" : "")." class=\"bg".$i."\"></option>\n";
		}
		$styleform = "<select name=\"style_color$styleid\" id=\"style_color$styleid\" onchange=\"document.all.style_id$styleid.value=document.all.style_color$styleid.value;if(document.all.style_strong$styleid.checked)document.all.style_id$styleid.value += ' '+document.all.style_strong$styleid.value;\">\n".$styleform."</select>\n";
		$styleform .= " <label><input type=\"checkbox\" name=\"style_strong$styleid\" id=\"style_strong$styleid\" value=\"b\" ".($b == 'b' ? "checked=\"checked\"" : "")." onclick=\"document.all.style_id$styleid.value=document.all.style_color$styleid.value;if(document.all.style_strong$styleid.checked)document.all.style_id$styleid.value += ' '+document.all.style_strong$styleid.value;\"> ".$LANG['bold'];
		$styleform .= "</label><input type=\"hidden\" name=\"".$name."\" id=\"style_id$styleid\" value=\"".$style."\">";
		return $styleform;
	}

	function text($name, $id = '', $value = '', $type = 'text', $size = 50, $class = '', $ext = '', $minlength = '', $maxlength = '', $pattern = '', $errortips = '')
	{
		if(!$id) $id = $name;
		$checkthis = '';
		$showerrortips = "字符长度必须为".$minlength."到".$maxlength."位";
		if($pattern)
		{
			$pattern = 'regexp="'.substr($pattern,1,-1).'"';
		}
		$require = $minlength ? 'true' : 'false';
		if($pattern && ($minlength || $maxlength))
		{
			$string_datatype = substr($string_datatype, 1);
			$checkthis = "require=\"$require\" $pattern datatype=\"limit|custom\" min=\"$minlength\" max=\"$maxlength\" msg='$showerrortips|$errortips'";
		}
		elseif($pattern)
		{
			$checkthis = "require=\"$require\" $pattern datatype=\"custom\" msg='$errortips'";
		}
		elseif($minlength || $maxlength)
		{
			$checkthis = "require=\"$require\" datatype=\"limit\" min=\"$minlength\" max=\"$maxlength\" msg='$showerrortips'";
		}
		return "<input type=\"$type\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $checkthis $ext/> ";
	}

	function textarea($name, $id = '', $value = '', $rows = 10, $cols = 50, $class = '', $ext = '', $character = 0, $maxlength = 0)
	{
		if(!$id) $id = $name;
		if($character && $maxlength)
		{
			$data = ' <img src="images/icon.gif" width="12"> 还可以输入 <font id="ls_'.$id.'" color="#ff0000;">'.$maxlength.'</font> 个字符！<br />';
		}
		$data .= "<textarea name=\"$name\" id=\"$id\" rows=\"$rows\" cols=\"$cols\" class=\"$class\" $ext>$value</textarea>";
		return $data;
	}

	function select($options, $name, $id = '', $value = '', $size = 1, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);
		if($size >= 1) $size = " size=\"$size\"";
		if($class) $class = " class=\"$class\"";
		$data .= "<select name=\"$name\" id=\"$id\" $size $class $ext>";
		foreach($options as $k=>$v)
		{
			$selected = $k == $value ? 'selected' : '';
			$data .= "<option value=\"$k\" $selected>$v</option>\n";
		}
		$data .= '</select>';
		return $data;
	}

	function multiple($options, $name, $id = '', $value = '', $size = 3, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);
		$size = max(intval($size), 3);
		if($class) $class = " class=\"$class\"";
		$value = strpos($value, ',') ? explode(',', $value) : array($value);
		$data .= "<select name=\"$name\" id=\"$id\" multiple=\"multiple\" size=\"$size\" $class $ext>";
		foreach($options as $k=>$v)
		{
			$selected = in_array($k, $value) ? 'selected' : '';
			$data .= "<option value=\"$k\" $selected>$v</option>\n";
		}
		$data .= '</select>';
		return $data;
	}

	function checkbox($options, $name, $id = '', $value = '', $cols = 5, $class = '', $ext = '', $width = 100)
	{
		if(!$options) return '';
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);
		$i = 1;
		$data = '<input type="hidden" name="'.$name.'" value="-99">';
		if($class) $class = " class=\"$class\"";
		if($value != '') $value = strpos($value, ',') ? explode(',', $value) : array($value);
             //$value = $value[0];  //henter修正
		foreach($options as $k=>$v)
		{
			$checked = ($value && in_array($k, $value)) ? 'checked' : '';
			$data .= "<span style=\"width:{$width}px\"><label><input type=\"checkbox\" boxid=\"{$id}\" name=\"{$name}[]\" id=\"{$id}\" value=\"{$k}\" style=\"border:0px\" $class {$ext} {$checked}/> {$v}</label></span>\n ";
			if($i%$cols == 0) $data .= "<br />\n";
			$i++;
		}
		return $data;
	}

	function radio($options, $name, $id = '', $value = '', $cols = 5, $class = '', $ext = '', $width = 100)
	{
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);
		$i = 1;
		$data = '';
		if($class) $class = " class=\"$class\"";
		foreach($options as $k=>$v)
		{
			$checked = $k == $value ? 'checked' : '';
			$data .= "<span style=\"width:{$width}px\"><label><input type=\"radio\" name=\"{$name}\" id=\"{$id}\" value=\"{$k}\" style=\"border:0px\" $class {$ext} {$checked}/> {$v}</label></span> ";
			if($i%$cols == 0) $data .= "<br />\n";
			$i++;
		}
		return $data;
	}

	function _option($options, $s1 = "\n", $s2 = '|')
	{
		$options = explode($s1, $options);
		foreach($options as $option)
		{
			if(strpos($option, $s2))
			{
				list($name, $value) = explode($s2, trim($option));
			}
			else
			{
				$name = $value = trim($option);
			}
			$os[$value] = $name;
		}
		return $os;
	}

	function image($name, $id = '', $value = '', $size = 50, $class = '', $ext = '', $modelid = 0, $fieldid = 0)
	{
              $idrand = rand();
		if(!$id) $id = $name.'_'.$idrand;
		$id .= '_'.$idrand;
		return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $ext/> <input type=\"hidden\" name=\"{$id}_aid\" value=\"0\"> <input type=\"button\" name=\"{$name}_upimage\" id=\"{$id}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('?file=upload_field&uploadtext={$id}&modelid={$modelid}&fieldid={$fieldid}','upload','350','350')\"/>";
	}

	function file($name, $id = '', $size = 50, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		return "<input type=\"file\" name=\"$name\" id=\"$id\" size=\"$size\" class=\"$class\" $ext/> ";
	}

	function downfile($name, $id = '', $value = '', $size = 50, $mode, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		$mode = "&mode=".$mode;
		if(defined('IN_ADMIN'))
		{
			return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $ext/> <input type=\"hidden\" name=\"{$id}_aid\" value=\"0\"> <input type=\"button\" name=\"{$name}_upfile\" id=\"{$id}_upfile\" value=\"上传文件\" style=\"width:60px\" onclick=\"javascript:openwinx('?file=upload&uploadtext={$id}{$mode}','upload','390','180')\"/>";
		}
		else
		{
			return true;
		}
	}

	function upload_image($name, $id = '', $value = '', $size = 50, $class = '', $property = '')
	{
		if(!$id) $id = $name;
		return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $property/> <input type=\"button\" name=\"{$name}_upimage\" id=\"{$id}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('?file=upload&uploadtext={$id}','upload','380','350')\"/>";
	}

	function select_template($module, $name, $id = '', $value = '', $property = '', $pre = '')
	{
		if(!$id) $id = $name;
		$templatedir = TPL_ROOT.TPL_NAME.'/'.$module.'/';
		$files = array_map('basename', glob($templatedir.$pre.'*.html'));
		$names = cache_read('name.inc.php', $templatedir);
		$templates = array(''=>'请选择');
		foreach($files as $file)
		{
			$key = substr($file, 0, -5);
			$templates[$key] = isset($names[$file]) ? $names[$file].'('.$file.')' : $file;
		}
		ksort($templates);
		return form::select($templates, $name, $id, $value, $property);
	}

	function select_file($name, $id = '', $value = '', $size = 30, $catcode = 0, $isimage = 0)
	{
		if(!$id) $id = $name;
		return "<input type='text' name='$name' id='$id' value='$value' size='$size' /> <input type='button' value='浏览...' style='cursor:pointer;' onclick=\"file_select('$id', $catcode, $isimage)\">";
	}

	function select_module($name = 'module', $id ='', $alt = '', $value = '', $property = '')
	{
		global $MODULE;
		if($alt) $arrmodule = array('0'=>$alt);
		foreach($MODULE as $k=>$v)
		{
			$arrmodule[$k] = $v['name'];
		}
		if(!$id) $id = $name;
		return form::select($arrmodule, $name, $id, $value, 1, '', $property);
	}

	function select_model($name = 'modelid', $id ='', $alt = '', $modelid = '', $property = '')
	{
		global $MODEL;
		if($alt) $arrmodel = array('0'=>$alt);
		foreach($MODEL as $k=>$v)
		{
			if($v['modeltype'] > 0) continue;
			$arrmodel[$k] = $v['name'];
		}
		if(!$id) $id = $name;
		return form::select($arrmodel, $name, $id, $modelid, 1, '', $property);
	}

	function select_member_model($name = 'modelid', $id = '', $alt = '', $modelid = '', $property = '')
	{
		global $MODEL;
		if($alt) $arrmodel = array('0'=>$alt);
		foreach($MODEL as $k=>$v)
		{
			if($v['modeltype'] == '2')
			{
				$arrmodel[$k] = $v['name'];
			}
		}
		if(!$id) $id = $name;
		return form::select($arrmodel, $name, $id, $modelid, 1, '', $property);
	}


	function select_pos($name = 'posid', $id ='', $posids = '', $cols = 1, $width = 100)
	{
		global $db,$priv_role, $POS;
		if(!$id) $id = $name;
		$pos = array();
		foreach($POS as $posid=>$posname)
		{
			//if($priv_role->check('posid', $posid)) $pos[$posid] = str_cut($posname, 16, '');
			$pos[$posid] = str_cut($posname, 16, '');
		}
		return form::checkbox($pos, $name, $id, $posids, $cols, '', '', $width);
	}

	function select_group($name = 'groupid', $id ='', $groupids = '', $cols = 1, $width = 100)
	{
		global $db, $GROUP;
		if(!$id) $id = $name;
		return form::checkbox($GROUP, $name, $id, $groupids, $cols, '', '', $width);
	}

	function select_type($module = 'dearcms', $name = 'typeid', $id ='', $alt = '', $typeid = 0, $property = '', $modelid = 0)
	{
		$types = subtype($module, $modelid);
		if(!$id) $id = $name;
		$data = "<select name='$name' id='$id' $property>\n<option value='0'>$alt</option>\n";
		foreach($types as $id=>$t)
		{
			$selected = $id == $typeid ? 'selected' : '';
			$data .= "<option value='$id' $selected>$t[name]</option>\n";
		}
		$data .= '</select>';
		return $data;
	}

	function select_area($name = 'areacode', $id ='', $alt = '', $parentid = 0, $areacode = 0)
	{
		global $AREA;
		$js = "<script type=\"text/javascript\">
					function area_load(id)
					{
						$.get('load.php', { field: 'areacode', id: id, value: '".$name."' },
							  function(data){
								$('#load_$name').append(data);
							  });

					}
					function area_reload()
					{
						$('#load_$name').html('');
						area_load(0);
					}
					area_load(0);
			</script>";
		if($areacode)
		{
			return "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$areacode\">
			<span onclick=\"this.style.display='none';\$('#reselect_$name').show();\" style=\"cursor:pointer;\">  ".areaname($areacode,1)."  <font color=\"red\">点击重选</font></span>
			<span id=\"reselect_$name\" style=\"display:none;\">
			<span id=\"load_$name\"></span> 
			<a href=\"javascript:area_reload();\">重选</a>
			</span>$js";
		}
		else
		{
			return "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$areacode\">
			<span id=\"load_$name\"></span>
			<a href=\"javascript:area_reload();\">重选</a>$js";
		}
	}



	function select_cat($name = 'catcode', $id ='', $alt = '', $parentid = 0, $catcode = 0,$mod)
	{
		global $CAT;
              $modfield = $mod ? ", mod: '".$mod."'" : '';
		$js = "<script type=\"text/javascript\">
					function cat_load(id)
					{
						$.get('load.php', { field: 'catcode', id: id, value: '".$name."' ".$modfield."},
							  function(data){
								$('#load_$name').append(data);
							  });

					}
					function cat_reload()
					{
						$('#load_$name').html('');
						cat_load(0);
					}
					cat_load(0);
			</script>";
		if($catcode)
		{
			return "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$catcode\">
			<span onclick=\"this.style.display='none';\$('#reselect_$name').show();\" style=\"cursor:pointer;\">  ".catname($catcode,1)."  <font color=\"red\">点击重选</font></span>
			<span id=\"reselect_$name\" style=\"display:none;\">
			<span id=\"load_$name\"></span> 
			<a href=\"javascript:cat_reload();\">重选</a>
			</span>$js";
		}
		else
		{
			return "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$catcode\">
			<span id=\"load_$name\"></span>
			<a href=\"javascript:cat_reload();\">重选</a>$js";
		}
	}


    function select_tpl($name = 'tpl', $type='main', $tpl = '', $id =''){
        global $TPLS;
        if(!$TPLS[$type]) return false;
        $str = "<select name='$name' id='$id'>";
        foreach($TPLS[$type] AS $k=>$v){
            //$k = ($k=='default') ? '' : $k;
            $selected = ($k==$tpl) ? ' selected' : '';
            $str .= "<option value='$k' $selected>$v[name]</option>";
        }
        $str .= "</select>";
        return $str;
    }



	function select_role($name, $value = '')
	{
            global $ROLE;

            $str = "<select name='$name' id='$id'>";
            foreach($ROLE AS $k=>$v){
                $selected = ($k==$value) ? ' selected' : '';
                $str .= "<option value='$k' $selected>$v[rolename]</option>";
            }
            $str .= "</select>";
            return $str;
	}

}

?>