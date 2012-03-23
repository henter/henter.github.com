	function images($field, $value)
	{
		global $attachment, $contentid, $show_url_path, $mod, $C, $DC, $MODULE;
		if(!$this->contentid) return false;
		$r = $this->data;
		$value = '';
		if(is_array($C))
		{
			$modelid = $C['modelid'];
		}
		else
		{
			$modelid = $this->modelid;
		}
		$modelcache = cache_read('model_'.$modelid.'.php');
        if(!is_object($attachment))
        {
            include 'attachment.class.php';
            $attachment = new attachment();
        }
		$images = $attachment->listinfo("contentid=$this->contentid AND field='$field'", 'aid,filepath,description','listorder,aid');
		$images_number = count($images);

		if($modelcache['ishtml'] && $this->fields[$field]['ishtml'] && $show_url_path)
		{
			$ishtml = 1;
			$array_images = $images;
			@extract($this->data);
			$updatetime = date('Y-m-d');
			$userid = $this->userid('userid', $userid);
			$copyfrom = $this->copyfrom('copyfrom',$copyfrom);
			$head['title'] = $title.' - '.$DC['sitename'];
			$head['keywords'] = $keywords;
			$head['description'] = $this->data['title'];
			ob_start();
			foreach($images AS $page=>$v)
			{
				$filename = DC_ROOT.$show_url_path.'_'.$page.'.'.$DC['fileext'];
				include template($GLOBALS['template_show_images']);
				$data = ob_get_contents();
				ob_clean();
				dir_create(dirname($filename));
				file_put_contents($filename, $data);
				@chmod($filename, 0777);
			}
		}
		else
		{
			foreach($images as $a)
			{
				$thumb = UPLOAD_URL.$attachment->get_thumb(UPLOAD_PATH.$a['filepath']);
				$url = 'images.php?aid='.$a['aid'];
				$value .= "<a href='$url' target='_blank'><img src='$thumb' border='0' alt='$a[description]'></a><br/>$a[description]<br/>";
			}
		}
		$GLOBALS['array_images'] = $images;
		$GLOBALS['images_number'] = $images_number;
		return $images;
	}
