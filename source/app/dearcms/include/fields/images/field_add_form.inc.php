<table cellpadding="2" cellspacing="1" onclick="javascript:$('#minlength').val(0);$('#maxlength').val(255);">
	<tr> 
      <td>允许上传的图片大小</td>
      <td><input type="text" name="setting[upload_maxsize]" value="1024" size="5">KB 提示：1KB=1024Byte，1MB=1024KB *</td>
    </tr>
	<tr> 
      <td>允许上传的图片类型</td>
      <td><input type="text" name="setting[upload_allowext]" value="gif|jpg|jpeg|png|bmp" size="40"></td>
    </tr>
	<tr> 
      <td>是否产生缩略图</td>
      <td><input type="radio" name="setting[isthumb]" value="1" <?=($DC['thumb_enable'] ? 'checked' : '')?> onclick="$('#thumb_size').show()"/> 是 <input type="radio" name="setting[isthumb]" value="0"  <?=($DC['thumb_enable'] ? '' : 'checked')?> onclick="$('#thumb_size').hide()"/> 否</td>
    </tr>
	<tr id="thumb_size" style="display:<?=($DC['thumb_enable'] ? 'block' : 'none')?>"> 
      <td>缩略图大小</td>
      <td>宽 <input type="text" name="setting[thumb_width]" value="<?=$DC['thumb_width']?>" size="3">px 高 <input type="text" name="setting[thumb_height]" value="<?=$DC['thumb_height']?>" size="3">px</td>
    </tr>
	<tr> 
      <td>是否加图片水印</td>
      <td><input type="radio" name="setting[iswatermark]" value="1" <?=($DC['watermark_enable'] ? 'checked' : '')?> onclick="$('#watermark_img').show()"/> 是 <input type="radio" name="setting[iswatermark]" value="0"  <?=($DC['watermark_enable'] ? '' : 'checked')?> onclick="$('#watermark_img').hide()"/> 否</td>
    </tr>
	<tr> 
      <td>是否生成静态</td>
      <td><input type="radio" name="setting[ishtml]" value="1" checked/> 是 <input type="radio" name="setting[ishtml]" value="0" /> 否<font color="#ff0000">内容页生成静态时，是否生成多页面</font></td>
    </tr>
	<tr id="watermark_img" style="display:<?=($DC['watermark_enable'] ? 'block' : 'none')?>"> 
      <td>水印图片路径</td>
      <td><input type="text" name="setting[watermark_img]" value="<?=$DC['watermark_img']?>" size="40"></td>
    </tr>
</table>