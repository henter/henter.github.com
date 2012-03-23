<table cellpadding="2" cellspacing="1" bgcolor="#ffffff">
	<tr> 
      <td><strong>时间格式：</strong></td>
      <td>
	  <input type="radio" name="setting[dateformat]" value="date" checked>日期（<?=date('Y-m-d')?>）<br />
	  <input type="radio" name="setting[dateformat]" value="datetime">日期+时间（<?=date('Y-m-d H:i:s')?>）<br />
	  <input type="radio" name="setting[dateformat]" value="int">整数 显示格式：
	  <select name="setting[format]">
	  <option value="Y-m-d H:i:s"><?=date('Y-m-d H:i:s')?></option>
	  <option value="Y-m-d H:i"><?=date('Y-m-d H:i')?></option>
	  <option value="Y-m-d"><?=date('Y-m-d')?></option>
	  <option value="m-d"><?=date('m-d')?></option>
	  </select>
	  </td>
    </tr>
	<tr> 
      <td><strong>默认值：</strong></td>
      <td>
	  <input type="radio" name="setting[defaulttype]" value="0" checked/>无<br />
	  <input type="radio" name="setting[defaulttype]" value="1"/>当前时间<br />
	  <input type="radio" name="setting[defaulttype]" value="2"/>指定时间：<input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="22"></td>
    </tr>
</table>