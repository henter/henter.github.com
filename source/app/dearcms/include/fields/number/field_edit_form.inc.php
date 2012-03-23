<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>取值范围</td>
      <td><input type="text" name="setting[minnumber]" value="<?=$minnumber?>" size="5"> - <input type="text" name="setting[maxnumber]" value="<?=$maxnumber?>" size="5"></td>
    </tr>
	<tr> 
      <td>小数位数：</td>
      <td>
	  <select name="setting[decimaldigits]">
	  <option value="-1" <?=($decimaldigits == -1 ? 'selected' : '')?>>自动</option>
	  <option value="0" <?=($decimaldigits == 0 ? 'selected' : '')?>>0</option>
	  <option value="1" <?=($decimaldigits == 1 ? 'selected' : '')?>>1</option>
	  <option value="2" <?=($decimaldigits == 2 ? 'selected' : '')?>>2</option>
	  <option value="3" <?=($decimaldigits == 3 ? 'selected' : '')?>>3</option>
	  <option value="4" <?=($decimaldigits == 4 ? 'selected' : '')?>>4</option>
	  <option value="5" <?=($decimaldigits == 5 ? 'selected' : '')?>>5</option>
	  </select>
    </td>
    </tr>
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="40"></td>
    </tr>
</table>