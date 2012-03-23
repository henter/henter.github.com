<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>下载模式</td>
      <td><input type="radio" name="setting[mode]" value="1"onclick="showservers(1)"/> 镜像模式 <input type="radio" name="setting[mode]" value="0" checked onclick="showservers(0)"/> 普通下载模式</td>
    </tr>
	<tr id="servers" style="display:none;"> 
      <td>服务器地址</td>
      <td><textarea name="setting[servers]" rows="3" cols="60"  style="height:80px;width:400px;"></textarea></td>
    </tr>
	<tr> 
      <td>字段长度</td>
      <td><input type="text" name="setting[size]" value="" size="5"></td>
    </tr>
	<tr> 
      <td>文件下载方式</td>
      <td><input type="radio" name="setting[downloadtype]" value="0" checked/> 链接文件地址 <input type="radio" name="setting[downloadtype]" value="1"/> 通过PHP读取</td>
    </tr>
</table>
<SCRIPT LANGUAGE="JavaScript">
<!--
	function showservers(flag)
	{
		if(flag) {
			$('#servers').css('display','');
		} else {
			$('#servers').css('display','none');
		}
	}
//-->
</SCRIPT>