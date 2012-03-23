<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>文本框长度</td>
      <td><input type="text" name="setting[size]" value="<?=$size?>" size="10"></td>
    </tr>
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="40"></td>
    </tr>
	<tr> 
      <td>允许上传的图片大小</td>
      <td><input type="text" name="setting[upload_maxsize]" value="<?=$upload_maxsize?>" size="5"> KB 提示：1 KB = 1024 Byte，1 MB = 1024 KB *</td>
    </tr>
	<tr> 
      <td>允许上传的图片类型</td>
      <td><input type="text" name="setting[upload_allowext]" value="<?=$upload_allowext?>" size="40"></td>
    </tr>

	<tr id="thumb_size" style="display:<?=($isthumb ? 'block' : 'none')?>"> 
      <td>缩略图大小</td>
      <td>宽 <input type="text" name="setting[thumb_width]" value="<?=$thumb_width?>" size="3">px 高 <input type="text" name="setting[thumb_height]" value="<?=$thumb_height?>" size="3">px</td>
    </tr>
	<tr> 
      <td>是否加图片水印</td>
      <td><input type="radio" name="setting[iswatermark]" value="1" <?=($iswatermark ? 'checked' : '')?> onclick="$('#watermark_img').show()"/> 是 <input type="radio" name="setting[iswatermark]" value="0"  <?=($iswatermark ? '' : 'checked')?> onclick="$('#watermark_img').hide()"/> 否</td>
    </tr>
</table>