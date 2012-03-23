<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>选项列表</td>
      <td><textarea name="setting[options]" rows="2" cols="20" id="options" style="height:100px;width:200px;"><?=$options?></textarea></td>
    </tr>
	<tr> 
      <td>选项类型</td>
      <td>
	  <input type="radio" name="setting[boxtype]" value="radio" <?=$boxtype == 'radio' ? 'checked' : ''?>  onclick="$('#setcols').show();$('#setsize').hide();"/> 单选按钮 <br />
	  <input type="radio" name="setting[boxtype]" value="checkbox" <?=$boxtype == 'checkbox' ? 'checked' : ''?>  onclick="$('#setcols').show();$('#setsize').hide();"/> 复选框 <br />
	  <input type="radio" name="setting[boxtype]" value="select" <?=$boxtype == 'select' ? 'checked' : ''?> onclick="$('#setcols').hide();$('#setsize').show();" /> 下拉框 <br />
	  <input type="radio" name="setting[boxtype]" value="multiple" <?=$boxtype == 'multiple' ? 'checked' : ''?>  onclick="$('#setcols').hide();$('#setsize').show();"/> 多选列表框
	  </td>
    </tr>
<tbody id="setcols" style="display:<?=($boxtype == 'radio' || $boxtype == 'checkbox') ? 'block' : 'none'?>">
	<tr> 
      <td>字段类型</td>
      <td>
	  <select name="setting[fieldtype]">
	  <option value="CHAR" <?=$fieldtype == 'CHAR' ? 'selected' : ''?>>定长字符 CHAR</option>
	  <option value="VARCHAR" <?=$fieldtype == 'VARCHAR' ? 'selected' : ''?>>变长字符 VARCHAR</option>
	  <option value="TINYINT" <?=$fieldtype == 'TINYINT' ? 'selected' : ''?>>整数 TINYINT(3)</option>
	  <option value="SMALLINT" <?=$fieldtype == 'SMALLINT' ? 'selected' : ''?>>整数 SMALLINT(5)</option>
	  <option value="MEDIUMINT" <?=$fieldtype == 'MEDIUMINT' ? 'selected' : ''?>>整数 MEDIUMINT(8)</option>
	  <option value="INT" <?=$fieldtype == 'INT' ? 'selected' : ''?>>整数 INT(10)</option>
	  </select>
	  </td>
    </tr>
	<tr> 
      <td>列数</td>
      <td><input type="text" name="setting[cols]" value="<?=$cols?>" size="5"> 每行显示的选项个数</td>
    </tr>
	<tr> 
      <td>每列宽度</td>
      <td><input type="text" name="setting[width]" value="<?=$width?>" size="5"> px</td>
    </tr>
</tbody>
<tbody id="setsize" style="display:<?=($boxtype == 'select' || $boxtype == 'multiple') ? 'block' : 'none'?>">
	<tr> 
      <td>高度</td>
      <td><input type="text" name="setting[size]" value="<?=$size?>" size="5"> 行</td>
    </tr>
</tbody>
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="40"></td>
    </tr>
</table>