<?php
class seccode
{
	public $consts = 'bcdfhjkmnpqrstwxyz', 
	       $vowels = 'aei23456789',
	       $height = 24,
	       $length = 4,
	       $angle = 10,		//倾斜度
	       $contort = 2,	//扭曲度
	       $fonts;

	function __construct()
	{
		$session = & factory::session();
		$session->start();
		$this->fonts = CMSTOP_PATH.'resources/fonts/couri.ttf';
	}
	
	function image()
	{
		$string = $this->_string();
		$this->_image($string);
	}
	
	function valid($destroy = false)
	{
		$result = (isset($_SESSION['seccode']) && $_REQUEST['seccode'] === $_SESSION['seccode']) ? true : false;
		if ($destroy) unset($_SESSION['seccode']);
		return $result;
	}
	
	function _string()
	{
		$constslen = strlen($this->consts) - 1;
		$vowelslen = strlen($this->vowels) - 1;
		$string = '';
		for ($x = 0; $x < $this->length; $x++)
		{
			$string .= $x%2 == 0 ? substr($this->consts, mt_rand(0, $constslen), 1) : substr($this->vowels, mt_rand(0, $vowelslen), 1);
		}
		$_SESSION['seccode'] = $string;
		return $_SESSION['seccode'];
	}
	
	function _image($string)
	{
		ob_clean();
		$imageX = strlen($string)*13;	//the image width
		$imageY = $this->height;						//the image height
		$im = imagecreatetruecolor($imageX, $imageY);

		//背景
		imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
		
		//角度旋转写入
		$fontColor = imagecolorallocate($im, 0, 0, 192);
		for($i=0; $i<strlen($string); $i++)
		{
			$angle = mt_rand(-$this->angle, $this->angle);	//角度随机
			$fontsize = mt_rand(12, 16);	//字体大小随机
			imagefttext ($im , $fontsize , $angle , 2+$i*11 , 18 , $fontColor, $this->fonts , $string[$i]);
		}
		
		
		//扭曲
		$dstim = imagecreatetruecolor ($imageX , $imageY);       
		imagefill($dstim, 0, 0, imagecolorallocate($dstim,255,255,255) );
		
		$this->contort = mt_rand(1, $this->contort);
		$funcs = array('sin', 'cos');
		$func = $funcs[mt_rand(0, 1)];
		for ( $j=0; $j<$imageY; $j++) {
			$amend = round($func($j/$imageY*2*M_PI-M_PI*0.5) * $this->contort);
			for ( $i=0; $i<$imageX; $i++) {
				$rgb = imagecolorat($im, $i , $j);
				imagesetpixel($dstim, $i+$amend, $j, $rgb);
			}
		}
		
		//边框
		$border = imagecolorallocate($dstim, 133, 153, 193);
		imagerectangle($dstim, 0, 0, $imageX - 1, $imageY - 1, $border);
		
		header("content-type:image/png\r\n");
		imagepng($dstim);
		imagedestroy($im);
		imagedestroy($dstim);
	}
}