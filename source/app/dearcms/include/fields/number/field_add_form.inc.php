<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>取值范围</td>
      <td><input type="text" name="setting[minnumber]" value="1" size="5"> - <input type="text" name="setting[maxnumber]" value="" size="5"></td>
    </tr>
	<tr> 
      <td>小数位数：</td>
      <td>
	  <select name="setting[decimaldigits]">
	  <option value="-1">自动</option>
	  <option value="0" selected>0</option>
	  <option value="1">1</option>
	  <option value="2">2</option>
	  <option value="3">3</option>
	  <option value="4">4</option>
	  <option value="5">5</option>
	  </select>
    </td>
    </tr>
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="40"></td>
    </tr>
</table>