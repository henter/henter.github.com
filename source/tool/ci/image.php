<?php
define('WATERMARK_DIR', CMSTOP_PATH.'resources'.DS.'watermark'.DS);

class image extends object 
{
	public $source,
	       $thumb_width = null,
	       $thumb_height = null,
	       $thumb_quality = 100,
	       $watermark,
	       $watermark_ext,
	       $watermark_im,
	       $watermark_width,
	       $watermark_height,
	       $watermark_minwidth = 300,
	       $watermark_minheight = 300,
	       $watermark_position = 9,
	       $watermark_trans = 65,
	       $watermark_quality = 100;
	       
	private $imginfo,
	        $imagecreatefromfunc,
	        $imagefunc,
	        $animatedgif = 0;
	
	function set_source($source)
	{
		if (!file_exists($source)) return false;
		$this->source = $source;
		$this->animatedgif = false;
		$this->imginfo = @getimagesize($this->source);
		switch($this->imginfo['mime'])
		{
			case 'image/jpeg':
				$this->imagecreatefromfunc = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
				$this->imagefunc = (imagetypes() & IMG_JPG) ? 'imagejpeg' : '';
				break;
			case 'image/gif':
				$this->imagecreatefromfunc = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
				$this->imagefunc = (imagetypes() & IMG_GIF) ? 'imagegif' : '';
				break;
			case 'image/png':
				$this->imagecreatefromfunc = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
				$this->imagefunc = (imagetypes() & IMG_PNG) ? 'imagepng' : '';
				break;
		}
		if($this->imginfo['mime'] == 'image/gif') 
		{
			if($this->imagecreatefromfunc && !@imagecreatefromgif($this->source)) 
			{
				$this->errno = 1;
				$this->imagecreatefromfunc = $this->imagefunc = '';
				return false;
			}
			$this->animatedgif = strpos(file_get_contents($this->source), 'NETSCAPE2.0') === false ? false : true;
		}
		return !$this->animatedgif;
	}
	
	function set_thumb($width = null, $height = null, $quality = 100)
	{
		$this->thumb_width = is_null($width) ? null : intval($width);
		$this->thumb_height = is_null($height) ? null : intval($height);
		$this->thumb_quality = min(100, intval($quality));
	}
	
	function thumb($source, $target = null)
	{
		if(!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || !function_exists('imagejpeg') || !$this->set_source($source)) return false;

		list($img_w, $img_h) = $this->imginfo;
		if((is_null($this->thumb_width) || $img_w <= $this->thumb_width) && (is_null($this->thumb_height) || $img_h <= $this->thumb_height)) return false;

		if (is_null($target)) $target = $this->source;

		$thumb_w = $this->thumb_width ? $this->thumb_width : $img_w;
		$thumb_h = $this->thumb_height ? $this->thumb_height : $img_h;
		$x_ratio = $thumb_w / $img_w;
		$y_ratio = $thumb_h / $img_h;
		if(($x_ratio * $img_h) < $thumb_h)
		{
			$thumb['width'] = $thumb_w;
			$thumb['height'] = ceil($x_ratio * $img_h);
		}
		else
		{
			$thumb['width'] = ceil($y_ratio * $img_w);
			$thumb['height'] = $thumb_h;
		}
		$cx = $img_w;
		$cy = $img_h;
		
		$imagecreatefromfunc = $this->imagecreatefromfunc;
		$img_photo = $imagecreatefromfunc($this->source);
		$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);
		imagecopyresampled($thumb_photo, $img_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $cx, $cy);
		clearstatcache();
		
		$imagefunc = $this->imagefunc;
		$result = $this->imginfo['mime'] == 'image/jpeg' ? $imagefunc($thumb_photo, $target, $this->thumb_quality) : $imagefunc($thumb_photo, $target);
		return $result;
	}
	
	function set_watermark($watermark, $minwidth = null, $minheight = null, $position = null, $trans = null, $quality = null)
	{
		if (!file_exists($watermark)) return false;
		
		$this->watermark = $watermark;
		$this->watermark_ext = strtolower(pathinfo($watermark, PATHINFO_EXTENSION));
		if (!in_array($this->watermark_ext, array('gif', 'png')) || !is_readable($this->watermark)) return false;
		
		$this->watermark_im	= $this->watermark_ext == 'png' ? @imagecreatefrompng($this->watermark) : @imagecreatefromgif($this->watermark);
		if(!$this->watermark_im) return false;
		
		$watermarkinfo	= @getimagesize($this->watermark);
		$this->watermark_width	= $watermarkinfo[0];
		$this->watermark_height	= $watermarkinfo[1];
		
		if (!is_null($minwidth)) $this->watermark_minwidth = intval($minwidth);
		if (!is_null($minheight)) $this->watermark_minheight = intval($minheight);
		if (!is_null($position)) $this->watermark_position = intval($position);
		if (!is_null($trans)) $this->watermark_trans = min(100, intval($trans));
		if (!is_null($quality)) $this->watermark_quality = min(100, intval($quality));
	}
	
	function watermark($source, $target = null)
	{
		if (!$this->set_source($source) || ($this->watermark_minwidth && $this->imginfo[0] <= $this->watermark_minwidth) || ($this->watermark_minheight && $this->imginfo[1] <= $this->watermark_minheight) || !function_exists('imagecopy') || !function_exists('imagealphablending') || !function_exists('imagecopymerge')) return false;
	
		if (is_null($target)) $target = $source;

		list($img_w, $img_h) = $this->imginfo;

		$wmwidth = $img_w - $this->watermark_width;
		$wmheight = $img_h - $this->watermark_height;
		if($wmwidth < 10 || $wmheight < 10) return false;

		switch($this->watermark_position)
		{
			case 1:
				$x = +5;
				$y = +5;
				break;
			case 2:
				$x = $wmwidth / 2;
				$y = +5;
				break;
			case 3:
				$x = $wmwidth - 5;
				$y = +5;
				break;
			case 4:
				$x = +5;
				$y = $wmheight / 2;
				break;
			case 5:
				$x = $wmwidth / 2;
				$y = $wmheight / 2;
				break;
			case 6:
				$x = $wmwidth;
				$y = $wmheight / 2;
				break;
			case 7:
				$x = +5;
				$y = $wmheight - 5;
				break;
			case 8:
				$x = $wmwidth / 2;
				$y = $wmheight - 5;
				break;
			default:
				$x = $wmwidth - 5;
				$y = $wmheight - 5;
		}
		
		$im = imagecreatetruecolor($img_w, $img_h);
		$imagecreatefromfunc = $this->imagecreatefromfunc;
		$source_im = @$imagecreatefromfunc($this->source);
		imagecopy($im, $source_im, 0, 0, 0, 0, $img_w, $img_h);
			
		if($this->watermark_ext == 'png')
		{
			imagecopy($im, $this->watermark_im, $x, $y, 0, 0, $this->watermark_width, $this->watermark_height);
		}
		else
		{
			imagealphablending($this->watermark_im, true);
			imagecopymerge($im, $this->watermark_im, $x, $y, 0, 0, $this->watermark_width, $this->watermark_height, $this->watermark_trans);
		}
		clearstatcache();
		
		$imagefunc = $this->imagefunc;
		$result = $this->imginfo['mime'] == 'image/jpeg' ? $imagefunc($im, $target, $this->watermark_quality) : $imagefunc($im, $target);
		@imagedestroy($im);
		return $result;
	}
}