	function shopid($field, $value)
	{
		return '<a href="'.shop_url($value).'" target="_blank" class="shopname">'.$this->data['shopname'].'</a>';
	}
