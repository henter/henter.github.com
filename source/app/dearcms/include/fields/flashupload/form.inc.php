	function flashupload($field, $value, $fieldinfo)
	{
	    global $attachment, $player;
		if(!is_a($player, 'player'))
		{
			$player = load('player.class.php');
		}
		$arr_player = $player->listinfo('disabled=0');
		$session_id = session_id();
		$cookie_auth = get_cookie('auth');
		$cookie_cookietime = get_cookie('cookietime');
	    @extract($fieldinfo);
		if($upload_allowext)
		{
			$org_upload_allowext = $upload_allowext;
			$arr_allowext = explode('|', $upload_allowext);
			foreach($arr_allowext as $k=>$v)
			{
				$v = '*.'.$v;
				$array[$k] = $v;
			}
			$upload_allowext = implode(';', $array);
		}
		$firstid = $field.'1';
	    $data = '';
	    if(!$value)
	    {
	    	 $value = $defaultvalue;
	    }
		else
		{
			$value = str_replace(array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'),array('&', '"', "'", '<', '>') ,$value);
			eval("\$value = $value;");
			$playid = $value['player'];
			@extract($value);
			$value['str_video'] = str_replace(';', "\n", $value['str_video']);
		}
		
	    $script = "
		<textarea name=\"info[$field][videourl]\" id=\"videoshow\" cols=\"80\" rows=\"5\" />".$value['str_video']."</textarea>
		<link href=\"admin/skin/default.css\" rel=\"stylesheet\" type=\"text/css\">
		<script language=\"JavaScript\" src=\"images/js/swfupload/swfupload.js\"></script>
		<script language=\"JavaScript\" src=\"images/js/swfupload/fileprogress.js\"></script>
		<script language=\"JavaScript\" src=\"images/js/swfupload/hanlders.js\"></script>
		<script language=\"javascript\">
		var swfu;
window.onload = function() {
var settings = {
	upload_url : \"flash_upload.php?dosubmit=1\",
	flash_url : \"images/js/swfupload/swfupload.swf\",
	post_params: 
	{\"PHPSESSID\" : \"$session_id\",
	 \"auth\" : \"$cookie_auth\",
	 \"cookietime\" : \"$cookie_cookietime\",
	 \"modelid\" : \"$this->modelid\",
	 \"fieldid\" : \"$fieldid\"},
	file_size_limit : \"$upload_maxsize KB\",
	file_types : \"$upload_allowext\",
	file_types_description : \"All Files\",
	file_upload_limit : 100,
	file_queue_limit : \"$upload_items\",
	custom_settings : {
		progressTarget : \"fsUploadProgress\",
		cancelButtonId : \"btnCancel\"
	},
	debug: false,
	
	button_image_url: \"images/flash_button.gif\",	// Relative to the Flash file
	button_placeholder_id: \"spanButtonPlaceHolder\",
	button_width: 80,
	button_height: 22,
	
	file_dialog_start_handler : fileDialogStart,
	file_queued_handler : fileQueued,
	file_queue_error_handler : fileQueueError,
	file_dialog_complete_handler : fileDialogComplete,
	upload_progress_handler : uploadProgress,
	upload_error_handler : uploadError,
	upload_success_handler : uploadSuccess,
	upload_complete_handler : uploadComplete
};
swfu = new SWFUpload(settings);
	};
var n = 1;
function uploadSuccess(file, serverData) {
	try {
		if(serverData==1)
		{
			alert('上传的文件超过了 php.ini 中 upload_max_filesize=".ini_get('upload_max_filesize')." 选项限制的值');
			return false;
		}
		else if(serverData==99)
		{
			alert('上传的文件超过了 php.ini 中 post_max_size=".ini_get('post_max_size')."选项限制的值');
			return false;
		}
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setComplete();
		progress.setStatus(\"上传完成\");
		progress.toggleCancel(false);
		if($('#videoshow').html())
		{
			document.getElementById('videoshow').value += serverData + \"\\n\";
		}
		else
		{
			document.getElementById('videoshow').value = serverData + \"\\n\";
		}	
	} catch (ex) {
		this.debug(ex);
	}
}

function setvideo()
{
	var i = 0;
	var data = '';
	var startnum = parseInt($('#startvideo').val());
	var endnum = parseInt($('#endvideo').val());
	var videourl = $('#playurl').val();
	var videoext = $('#vext').val();
	for(i=startnum; i<=endnum; i++)
	{
		data = i + '|' +videourl + i +videoext;
		document.getElementById('videoshow').value += data + \"\\n\";
	}
}
			</script>
    <fieldset class=\"flash\" id=\"fsUploadProgress\">
			<legend>上传列表</legend>
			</fieldset>
<span id=\"spanButtonPlaceHolder\"></span>&nbsp;
<input type=\"button\" id=\"btupload\" value=\"开始上传\" onClick=\"swfu.startUpload();\" />
<input id=\"btnCancel\" type=\"button\" value=\"取消上传\" onClick=\"cancelQueue(upload1);\" disabled=\"disabled\" style=\"margin-left: 2px; height: 22px; font-size: 8pt;\" />";
	foreach($arr_player as $play)
	{
		$arr_p[$play['playerid']] = $play['subject']; 
	}
	if($arr_p)
	{
		$arr_p[''] = '自动选择播放器';
		ksort($arr_p);
		$sele_player = form::select($arr_p, 'info['.$field.'][player]', 'player', $player);
	}
	if($servers)
	{
		$sele_server = form::select($servers, 'info['.$field.'][server]', 'player', $server);
	}
	$op_server = $sele_player.'&nbsp;'.$sele_server.'<br />'.'播放地址：<input type="text" name="playurl" id="playurl" >开始集数：<input type="text" name="startvideo" id="startvideo" value="1">结束：<input type="text" name="endvideo" id="endvideo" value="1">视频格式：<input type="text" name="vext" id="vext" value=".rm">&nbsp;<input type="button" value="设定" onclick="setvideo()">';
	$data = $op_server.'<br />'.$script;
	    return  $data;
	}