<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>编辑器样式</td>
      <td><input type="radio" name="setting[toolbar]" value="basic" <?=($toolbar == 'basic' ? 'checked' : '')?> /> 简洁型 <input type="radio" name="setting[toolbar]" value="standard" <?=($toolbar == 'standard' ? 'checked' : '')?> /> 标准型 <input type="radio" name="setting[toolbar]" value="full" <?=($toolbar == 'full' ? 'checked' : '')?> /> 全功能</td>
    </tr>
	<tr> 
      <td>编辑器大小</td>
      <td>宽 <input type="text" name="setting[width]" value="<?=$width?>" size="4"> px 高 <input type="text" name="setting[height]" value="<?=$height?>" size="4"> px</td>
    </tr>
	<tr> 
      <td>默认值</td>
      <td><textarea name="setting[defaultvalue]" rows="2" cols="20" id="defaultvalue" style="height:100px;width:250px;"><?=$defaultvalue?></textarea></td>
    </tr>
	<tr> 
      <td>数据存储方式：</td>
      <td><input type="radio" name="setting[storage]" value="database" <?=$storage == 'database' ? 'checked' : ''?> /> 数据库存储 <input type="radio" name="setting[storage]" value="file" <?=$storage == 'file' ? 'checked' : ''?> /> 文本存储</td>
    </tr>
	<tr> 
      <td>是否保存远程图片：</td>
      <td><input type="radio" name="setting[enablesaveimage]" value="1" <?=($enablesaveimage == 1 ? 'checked' : '')?> /> 是 <input type="radio" name="setting[enablesaveimage]" value="0" <?=($enablesaveimage == 0 ? 'checked' : '')?> /> 否</td>
    </tr>
</table>