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
      <td>是否为密码框</td>
      <td><input type="radio" name="setting[ispassword]" value="1" <?=($ispassword ? 'checked' : '')?> /> 是 <input type="radio" name="setting[ispassword]" value="0" <?=($ispassword ? '' : 'checked')?> /> 否</td>
    </tr>
</table>