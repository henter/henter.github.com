<table cellpadding="2" cellspacing="1" onclick="javascript:$('#minlength').val(0);$('#maxlength').val(255);">
	<tr> 
      <td>允许上传的视频大小</td>
      <td><input type="text" name="setting[upload_maxsize]" value="<?=$upload_maxsize?>" size="5">KB 提示：1KB=1024Byte，1MB=1024KB *<br />服务器允许的最大上传附件为<font color="red"><?=ini_get('upload_max_filesize')?></font>，你所设的值需小于或等于<font color="red"><?=ini_get('upload_max_filesize')?></font></td>
    </tr>
	<tr> 
      <td>允许上传视频类型</td>
      <td><input type="text" name="setting[upload_allowext]" value="<?=$upload_allowext?>" size="40"></td>
    </tr>
	<tr>
	  <td>允许上传的视频个数</td>
	  <td><input type="text" name="setting[upload_items]" value="<?=$upload_items?>" size="10" /></td>
	</tr>
	<tr>
	  <td>视频服务器</td>
	  <td><textarea name="setting[servers]" rows="3" cols="60"  style="height:80px;width:400px;"><?=$servers?></textarea></td>
	</tr>
	<tr>
		<td>播放器代码</td>
		<td><a href="?file=player&action=manage">管理播放器代码</a></td>
	</tr>
</table>