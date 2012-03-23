<table cellpadding="2" cellspacing="1" bgcolor="#ffffff">
	<tr> 
      <td><strong>时间格式：</strong></td>
      <td>
	  <input type="radio" name="setting[dateformat]" value="date" <?=($dateformat == 'date' ? 'checked' : '')?> />日期（<?=date('Y-m-d')?>）<br />
	  <input type="radio" name="setting[dateformat]" value="datetime" <?=($dateformat == 'datetime' ? 'checked' : '')?> />日期+时间（<?=date('Y-m-d H:i:s')?>）<br />
	  <input type="radio" name="setting[dateformat]" value="int" <?=($dateformat == 'int' ? 'checked' : '')?> />整数 显示格式：
	  <select name="setting[format]">
	  <option value="Y-m-d H:i:s" <?=($format == 'Y-m-d H:i:s' ? 'selected' : '')?>><?=date('Y-m-d H:i:s')?></option>
	  <option value="Y-m-d H:i" <?=($format == 'Y-m-d H:i' ? 'selected' : '')?>><?=date('Y-m-d H:i')?></option>
	  <option value="Y-m-d" <?=($format == 'Y-m-d' ? 'selected' : '')?>><?=date('Y-m-d')?></option>
	  <option value="m-d" <?=($format == 'm-d' ? 'selected' : '')?>><?=date('m-d')?></option>
	  </select>
	  </td>
    </tr>
	<tr> 
      <td><strong>默认值：</strong></td>
      <td>
	  <input type="radio" name="setting[defaulttype]" value="0" <?=($defaulttype == 0 ? 'checked' : '')?>/> 无<br />
	  <input type="radio" name="setting[defaulttype]" value="1" <?=($defaulttype == 1 ? 'checked' : '')?>/> 当前时间<br />
	  <input type="radio" name="setting[defaulttype]" value="2" <?=($defaulttype == 2 ? 'checked' : '')?>/> 指定时间  <input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="19"></td>
    </tr>
</table>