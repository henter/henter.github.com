<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>文本框长度</td>
      <td><input type="text" name="setting[size]" value="30" size="5"></td>
    </tr>
	<tr> 
      <td>允许上传的文件大小</td>
      <td><input type="text" name="setting[upload_maxsize]" value="1024" size="5"> KB 提示：1 KB = 1024 Byte，1 MB = 1024 KB *</td>
    </tr>
	<tr> 
      <td>允许上传的文件类型</td>
      <td><input type="text" name="setting[upload_allowext]" value="zip|rar|doc|docx|xls|ppt|txt|gif|jpg|jpeg|png|bmp" size="50"></td>
    </tr>
	<tr> 
      <td>是否保存文件大小</td>
      <td><input type="radio" name="setting[issavefilesize]" value="1"> 是 <input type="radio" name="setting[issavefilesize]" value="0" checked> 否</td>
    </tr>
	<tr> 
      <td>文件下载方式</td>
      <td><input type="radio" name="setting[downloadtype]" value="0"> 链接文件地址 <input type="radio" name="setting[downloadtype]" value="1" checked> 通过PHP读取</td>
    </tr>
</table>