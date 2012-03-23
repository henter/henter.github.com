	function file($field, $value)
	{
		global $DC,$aids,$mod,$catcode,$attachment,$contentid;
		$upload_maxsize = 1024*$this->fields[$field]['upload_maxsize'];
		if($contentid) $result = $attachment->listinfo("contentid=$contentid AND field='$field'", 'aid');
		$aids = $attachment->upload($field, $this->fields[$field]['upload_allowext'], $upload_maxsize, 1);
		if(!$aids) return $result ? 1 : 0;
		return UPLOAD_URL.$attachment->attachments[$field][$aids[0]];
	}
