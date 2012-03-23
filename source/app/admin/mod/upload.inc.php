<?php 
defined('IN_DC') or exit('Access Denied');
require_once 'attachment.class.php';
$attachment = new attachment($mod);

$upload_allowext = UPLOAD_ALLOWEXT;
$upload_maxsize = UPLOAD_MAXSIZE;
$isthumb = $isthumb ? 1 : 0;
$thumb_width = isset($width) ? $width : $DC['pic_thumb']['small']['width'];
$thumb_height = isset($height) ? $height : $DC['pic_thumb']['small']['height'];
$iswatermark = $DC['watermark'] && $info['iswatermark'] ? 1 : 0;
$watermark_img = DC_ROOT.'images/watermark.png';

if($dosubmit)
{
	$attachment->upload('uploadfile', $upload_allowext, $upload_maxsize, 1);
	if($attachment->error) showmessage($attachment->error());
	$imgurl = UPLOAD_URL.$attachment->uploadedfiles[0]['filepath'];
	$aid = $attachment->uploadedfiles[0]['aid'];
	$filesize = $attachment->uploadedfiles[0]['filesize'];
	$filesize = $attachment->size($filesize);
    if($isthumb || $iswatermark)
	{
		require_once 'image.class.php';
		$image = new image();
		$img = UPLOAD_ROOT.$attachment->uploadedfiles[0]['filepath'];
		if($isthumb)
		{
			$image->thumb($img, $img, $thumb_width, $thumb_height);
		}
		if($iswatermark)
		{
            		$image->watermark($img, $img, 9, $watermark_img, '', 5, '#ff0000', 80);
		}
	}
	showmessage("文件上传成功！<script language='javascript'>	try{ $(window.opener.document).find(\"form[@name='myform'] #$uploadtext\").val(\"$imgurl\");$(window.opener.document).find(\"form[@name='myform'] #{$uploadtext}_aid\").val(\"$aid\");$(window.opener.document).find(\"form[@name='myform'] #$filesize\").val(\"$filesize\");}catch(e){} window.close();</script>", HTTP_REFERER);
}
else
{
	include atpl('upload');
}
?>