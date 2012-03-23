<table cellpadding="2" cellspacing="1" onclick="javascript:$('#minlength').val(0);$('#maxlength').val(255);">
	<tr> 
      <td>选项列表</td>
      <td><textarea name="setting[options]" rows="2" cols="20" id="options" style="height:100px;width:200px;">选项名称1|选项值1</textarea></td>
    </tr>
	<tr> 
      <td>选项类型</td>
      <td>
	  <input type="radio" name="setting[boxtype]" value="radio" checked onclick="$('#setcols').show();$('#setsize').hide();"/> 单选按钮 <br />
	  <input type="radio" name="setting[boxtype]" value="checkbox" onclick="$('#setcols').show();$('setsize').hide();"/> 复选框 <br />
	  <input type="radio" name="setting[boxtype]" value="select" onclick="$('#setcols').hide();$('setsize').show();" /> 下拉框 <br />
	  <input type="radio" name="setting[boxtype]" value="multiple" onclick="$('#setcols').hide();$('setsize').show();" /> 多选列表框
	  </td>
    </tr>
<tbody id="setcols" style="display:block">
	<tr> 
      <td>字段类型</td>
      <td>
	  <select name="setting[fieldtype]">
	  <option value="CHAR">定长字符 CHAR</option>
	  <option value="VARCHAR">变长字符 VARCHAR</option>
	  <option value="TINYINT">整数 TINYINT(3)</option>
	  <option value="SMALLINT">整数 SMALLINT(5)</option>
	  <option value="MEDIUMINT">整数 MEDIUMINT(8)</option>
	  <option value="INT">整数 INT(10)</option>
	  </select>
	  </td>
    </tr>
	<tr> 
      <td>列数</td>
      <td><input type="text" name="setting[cols]" value="5" size="5"> 每行显示的选项个数</td>
    </tr>
	<tr> 
      <td>每列宽度</td>
      <td><input type="text" name="setting[width]" value="80" size="5"> px</td>
    </tr>
</tbody>
<tbody id="setsize" style="display:none">
	<tr> 
      <td>高度</td>
      <td><input type="text" name="setting[size]" value="1" size="5"> 行</td>
    </tr>
</tbody>
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" size="40"></td>
    </tr>
</table>