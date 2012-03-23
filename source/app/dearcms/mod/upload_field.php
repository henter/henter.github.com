<?php
require 'inc/common.inc.php';
if(!$modelid || !$fieldid) exit;
$modelid = intval($modelid);
$fieldid = intval($fieldid);
require_once 'attachment.class.php';
require_once 'admin/model_field.class.php';

$field = new model_field($modelid);
$info = $field->get($fieldid);
if(!$info) showmessage('指定的字段不存在！');
$upload_allowext = $info['upload_allowext'];
$upload_maxsize = $info['upload_maxsize']*1024;
$isthumb = $isthumb ? 1 : 0;

$iswatermark = $DC['watermark'] && $info['iswatermark'] ? 1 : 0;
$thumb_width = isset($width) ? $width : $DC['pic_thumb']['small']['width'];
$thumb_height = isset($height) ? $height : $DC['pic_thumb']['small']['height'];

$watermark_img = DC_ROOT.'images/watermark.png';

$attachment = new attachment($mod);


if($dosubmit)
{
	$attachment->upload($uploadtext, $upload_allowext, $upload_maxsize, 1);
	if($attachment->error) showmessage($attachment->error());
	$imgurl = UPLOAD_URL.$attachment->uploadedfiles[0]['filepath'];
	$aid = $attachment->uploadedfiles[0]['aid'];
	if($isthumb || $iswatermark)
	{
		require_once 'image.class.php';
		$image = new image();
		$img = UPLOAD_ROOT.$attachment->uploadedfiles[0]['filepath'];
		if($isthumb) $image->thumb($img, $img, $thumb_width, $thumb_height);
		if($iswatermark) $image->watermark($img, '', 9, $watermark_img, '', 5, '#ff0000', 80);
	}
	showmessage("文件上传成功！<script language='javascript'>$(window.opener.document).find(\"form[@name='myform'] #$uploadtext\").val(\"$imgurl\");$(window.opener.document).find(\"form[@name='myform'] #{$uploadtext}_aid\").val(\"$aid\");window.close();</script>", HTTP_REFERER);
}
else
{
	$upload_maxsize = $attachment->size($upload_maxsize);
	include template('upload_field');
}
?>