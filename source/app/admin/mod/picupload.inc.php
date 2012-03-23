<?php 
defined('IN_DC') or exit('Access Denied');

require_once 'attachment.class.php';

$attachment = new attachment($mod);

$upload_allowext = 'jpg|jpeg|gif|png';
$upload_maxsize = UPLOAD_MAXSIZE*2; //大图片 两倍于限制大小
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
    if($iswatermark)
	{
		require_once 'image.class.php';
		$image = new image();
		$img = UPLOAD_ROOT.$attachment->uploadedfiles[0]['filepath'];
		$image->watermark($img, '', 9, $watermark_img, '', 5, '#ff0000', 80);
	}
	showmessage("图片上传成功！<script language='javascript'>	try{ $(window.opener.document).find(\"form[@name='myform'] #$uploadtext\").val(\"$imgurl\");$(window.opener.document).find(\"form[@name='myform'] #{$uploadtext}_aid\").val(\"$aid\");$(window.opener.document).find(\"form[@name='myform'] #$filesize\").val(\"$filesize\");}catch(e){} window.close();</script>", HTTP_REFERER);
}
else
{
	include atpl('picupload');
}
?>