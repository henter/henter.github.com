	function images($field, $value, $fieldinfo)
	{
	    global $attachment;
		extract($fieldinfo);
		$data = '';
		$data .= "<div id='FilePreview' style='Z-INDEX: 1000; LEFT: 0px; WIDTH: 10px; POSITION: absolute; TOP: 0px; HEIGHT: 10px; display: none;'></div>\n";
		if(!$value)
		{
		    $value = $defaultvalue;
		}
		else
		{
            $data .= "<div id='file_uploaded'>\n";
			$attachments = $attachment->listinfo("`contentid`=$this->contentid AND `field`='$field'", '`aid`,`filename`,`filepath`,`description`,`listorder`,`isthumb`');
			foreach($attachments as $k=>$v)
			{
			    $aid = $v['aid'];
			    $url = $v['isthumb'] ? $attachment->get_thumb($v['filepath']) : $v['filepath'];
			    $data .= "<div id='file_uploaded_$aid'><span style='width:30px'><input type='checkbox' name='{$field}_delete[]' value='$aid' title='删除'></span><span style='width:40px'><input type='text' name='{$field}_listorder[$aid]' value='$v[listorder]' size='3' title='排序'></span><span style='width:60px'><input type='text' name='{$field}_description[$aid]' value='$v[description]' size='20' title='修改图片说明'></span> <a href='###' onMouseOut='javascript:FilePreview(\"$url\", 0);' onMouseOver='javascript:FilePreview(\"$url\", 1);'>$v[filename] ".($v['description'] ? '('.$v['description'].')' : '')."</a></div>\n";
			}
		    $data .= "</div>\n";
		}
		$addmorepic = '';
		if(defined('IN_ADMIN')) $addmorepic = '<input type="button" onclick="AddMorePic(\'addmore_'.$field.'\');" value="批量添加">';
		$data .= "<div id='addmore_$field'></div>";
		$data .= '<input type="hidden" name="info['.$field.']" value="'.$value.'"/>';
        $data .= '<div id="file_div">';
		$data .= '<div id="file_1"><input type="file" name="'.$field.'[1]" size="20" onchange="javascript:AddInputFile(\''.$field.'\')"> <input type="text" name="'.$field.'_description[1]" size="20" title="名称"> <input type="button" value="删除" name="Del" onClick="DelInputFile(1);"> 
		'.$addmorepic.'</div>';
		$data .= '</div>';
		$_SESSION['field_images'] = 1;
		return $data;
	}
