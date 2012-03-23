<table cellpadding="2" cellspacing="1" onclick="javascript:$('#minlength').val(0);$('#maxlength').val(255);">
	<tr> 
      <td>允许上传的图片大小</td>
      <td><input type="text" name="setting[upload_maxsize]" value="<?=$upload_maxsize?>" size="5"> KB 提示：1 KB = 1024 Byte，1 MB = 1024 KB *</td>
    </tr>
	<tr> 
      <td>允许上传的图片类型</td>
      <td><input type="text" name="setting[upload_allowext]" value="<?=$upload_allowext?>" size="40"></td>
    </tr>
	<tr> 
      <td>是否产生缩略图</td>
      <td><input type="radio" name="setting[isthumb]" value="1" <?=($isthumb ? 'checked' : '')?> onclick="$('#thumb_size').show()"/> 是 <input type="radio" name="setting[isthumb]" value="0" <?=($isthumb ? '' : 'checked')?> onclick="$('#thumb_size').hide()"/> 否</td>
    </tr>
	<tr id="thumb_size" style="display:<?=($isthumb ? 'block' : 'none')?>"> 
      <td>缩略图大小</td>
      <td>宽 <input type="text" name="setting[thumb_width]" value="<?=$thumb_width?>" size="3">px 高 <input type="text" name="setting[thumb_height]" value="<?=$thumb_height?>" size="3">px</td>
    </tr>
	<tr> 
      <td>是否加图片水印</td>
      <td><input type="radio" name="setting[iswatermark]" value="1" <?=($iswatermark ? 'checked' : '')?> onclick="$('#watermark_img').show()"/> 是 <input type="radio" name="setting[iswatermark]" value="0"  <?=($iswatermark ? '' : 'checked')?> onclick="$('#watermark_img').hide()"/> 否</td>
    </tr>
	<tr> 
      <td>是否生成静态</td>
      <td><input type="radio" name="setting[ishtml]" value="1" <?=($ishtml ? 'checked' : '')?>/> 是 <input type="radio" name="setting[ishtml]" value="0"  <?=($ishtml ? '' : 'checked')?>/> 否 <font color="#006600">内容页生成静态时，是否生成多页面</font></td>
    </tr>
	<tr id="watermark_img" style="display:<?=($iswatermark ? 'block' : 'none')?>"> 
      <td>水印图片路径</td>
      <td><input type="text" name="setting[watermark_img]" value="<?=$watermark_img?>" size="40"></td>
    </tr>
</table>