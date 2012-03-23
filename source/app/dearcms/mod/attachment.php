<?php 
require dirname(__FILE__).'/inc/common.inc.php';
require_once 'attachment.class.php';
require_once 'image.class.php';

if(!$_userid && !defined('IN_ADMIN') && !defined('IN_CP'))
{
	if($from == 'fckeditor')
	{
		$message = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET." \"><script language='javascript'>";
		$message .= "window.parent.show_ok('1-".$id."','没有上传权限！');";
		$message .= "</script>";
		//exit($message);
	}
	elseif($from == 'keditor')
	{
              //header('Content-type: text/html; charset='.CHARSET);
              //echo json_encode(array('error' => 1, 'message' => '没有上传权限！'));
              //exit;
	}
	else
	{
		showmessage('没有上传权限！');
	}
}

session_start();

$isthumb = $isthumb ? 1 : 0;
$iswatermark = $DC['watermark'];
$thumb_width = isset($width) ? $width : $DC['pic_thumb']['small']['width'];
$thumb_height = isset($height) ? $height : $DC['pic_thumb']['small']['height'];
$watermark_img = DC_ROOT.'images/watermark.png';


switch($action)
{
    case 'upload':
		if($dosubmit)
	        {
			$attachment = new attachment($module);
                    if($from == 'fckeditor'){
			    $aids = $attachment->upload('uploadfile', UPLOAD_ALLOWEXT, UPLOAD_MAXSIZE, 1);
                    }elseif($from == 'keditor'){
			    $aids = $attachment->upload('imgFile', UPLOAD_ALLOWEXT, UPLOAD_MAXSIZE, 1);
                    }else{
			    $aids = $attachment->upload('uploadfile', UPLOAD_ALLOWEXT, UPLOAD_MAXSIZE, 1);
                    }
			$filename = $attachment->uploadedfiles[0]['filename'];
			$filepath = $attachment->uploadedfiles[0]['filepath'];
			$fileurl = UPLOAD_URL.$filepath;
			$extension = fileext($filename);
			
			if($from == 'fckeditor')
			{
				$fileurl = url($fileurl);
				if(empty($filepath))
				{
					$message = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET."\"><script language='javascript'>";
					$message .= "window.parent.show_ok('1-".$id."','".$attachment->error()."');";
					$message .= "</script>";
					echo $message;exit;
				}
				if($iswatermark)
				{
					$imagefile = UPLOAD_ROOT.$filepath;
					$image = new image();
					$image->watermark($imagefile, $imagefile, 9, $watermark_img);
				}
				if($isthumb && in_array($extension,array('jpg','jpeg','gif','png')))
				{
					$imagefile = UPLOAD_ROOT.$filepath;
					$image = new image();
					$filename = $image->thumb($imagefile, $imagefile, $thumb_width, $thumb_height);

				}
				$filename = basename($filepath);

				if(isset($id)) 
				{
					$message = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET."\"><script language='javascript'>";
					$message .= "window.parent.show_ok('0-".$id."','".md5($fileurl)." $MM_objid $fileurl');";
					$message .= "window.parent.SetUrl('$fileurl', '', '', '$filename');";
					$message .= "</script>";
				}
				else
				{
					$message = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET."\"><script language='javascript'>";
					$message .= "window.parent.SetUrl('$fileurl', '', '', '$filename');";
					$message .= "</script>";
				}
			    exit($message);
			}elseif($from == 'keditor'){
				$fileurl = url($fileurl);
				if(empty($filepath))
				{
                                    header('Content-type: text/html; charset='.CHARSET);
                                    $msg = $attachment->error() ? $attachment->error() : '上传失败！';
                                    echo json_encode(array('error' => 1, 'message' => $msg));
                                    exit;
				}
				if($iswatermark)
				{
					$imagefile = UPLOAD_ROOT.$filepath;
					$image = new image();
					$image->watermark($imagefile, $imagefile, 9, $watermark_img);
				}
				if($isthumb && in_array($extension,array('jpg','jpeg','gif','png')))
				{
					$imagefile = UPLOAD_ROOT.$filepath;
					$image = new image();
					$filename = $image->thumb($imagefile, $imagefile, $thumb_width, $thumb_height);

				}
				$filename = basename($filepath);
                            //success
                             header('Content-type: text/html; charset='.CHARSET);
                             //echo $fileurl;exit;
                             echo json_encode(array('error' => 0, 'url' => $fileurl));
                             exit;
                    }
		}else{
			include template('attachment_upload');
		}
		break;

	case 'del_file':
		if(md5($filepath)==$verfiy) 
		{
			if(preg_match("%".UPLOAD_URL."%i",$filepath)) 
			{
				$attachment = new attachment($module);
				$filepath = str_replace('/'.UPLOAD_URL,'',$filepath);
				if($attachment->delete("filepath = '$filepath'"))
				{
					echo('ok');
				}
			}
		}
	break;
}
?>