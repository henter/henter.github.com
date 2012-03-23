	function keyword($field, $value)
	{
	    if($value == '') return '';
		$v = '';
		$tags = explode(' ', $value);
		foreach($tags as $tag)
		{
			$v .= '<a href="tag.php?tag='.urlencode($tag).'" class="keyword">'.$tag.'</a>';
		}
		return $v;
	}
