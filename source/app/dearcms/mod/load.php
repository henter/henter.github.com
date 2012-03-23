<?php 
define('IN_DC', TRUE);
define('NOCITYCODE', TRUE);
require dirname(__FILE__).'/inc/common.inc.php';

if($field == 'areacode')
{
    $a = get_cookie('admin') ? 0 : $citycode;
    $alen = strlen($a);
    if($alen == 6 && strlen($id)==6) $xxreturn = 1;

    $id = $id ? $id : $a;

       if($value){
	    $str = '<select onchange="$(\'#'.$value.'\').val(this.value);this.disabled=true;area_load(this.value);" name="areaselect"><option value="0">请选择地区</option>';
       }else{
	    $str = '<select onchange="$(\'#areacode\').val(this.value);this.disabled=true;areacode_load(this.value);" name="areaselect"><option value="0">请选择地区</option>';
       }

	$options = '';
       $len = $id ? (strlen($id) == 2 ? 4 : 6) : 2;

       $idlen = $id ? strlen($id) : 0;

	foreach($AREA as $i=>$v)
	{
        if($alen < strlen($i) && $alen!=6){
            
            if($idlen){
                if((strlen($i) == $len) && (substr($i, 0, $idlen) == $id) && $id!=$i) $options .= '<option value="'.$i.'">'.$v['areaname'].'</option>';
            }else{
                if(strlen($i) == $len) $options .= '<option value="'.$i.'">'.$v['areaname'].'</option>';
            }
            
        }elseif($alen==6){

                    if($id==$i && !$xxreturn) $options .= '<option value="'.$i.'">'.$v['areaname'].'</option>';

        }

	}
	if(empty($options)) exit;
	$str .= $options.'</select>';
        

/* old *
       if($value){
	    $str = '<select onchange="$(\'#'.$value.'\').val(this.value);this.disabled=true;area_load(this.value);" name="areaselect"><option value="0">请选择地区</option>';
       }else{
	    $str = '<select onchange="$(\'#areacode\').val(this.value);this.disabled=true;areacode_load(this.value);" name="areaselect"><option value="0">请选择地区</option>';
       }

	$options = '';
       $len = $id ? (strlen($id) == 2 ? 4 : 6) : 2;
       $idlen = $id ? strlen($id) : 0;

	foreach($AREA as $i=>$v)
	{
            if($idlen){
                if((strlen($i) == $len) && (substr($i, 0, $idlen) == $id) && $id!=$i) $options .= '<option value="'.$i.'">'.$v['areaname'].'</option>';
            }else{
                if(strlen($i) == $len) $options .= '<option value="'.$i.'">'.$v['areaname'].'</option>';
            }
	}
	if(empty($options)) exit;
	$str .= $options.'</select>';
/* old */

}
elseif($field == 'catcode')
{

       if($value){
	    $str = '<select onchange="$(\'#'.$value.'\').val(this.value);this.disabled=true;cat_load(this.value);" name="catselect"><option value="0">请选择栏目</option>';
       }else{
	    $str = '<select onchange="$(\'#catcode\').val(this.value);this.disabled=true;catcode_load(this.value);" name="catselect"><option value="0">请选择栏目</option>';
       }

	$options = '';
       $len = $id ? (strlen($id) == 2 ? 4 : 6) : 2;
       $idlen = $id ? strlen($id) : 0;

if($mod){
	foreach($CAT as $i=>$v)
	{
            if($v[module]==$mod){
                if($idlen){
                    if((strlen($i) == $len) && (substr($i, 0, $idlen) == $id) && $id!=$i) $options .= '<option value="'.$i.'">'.$v['catname'].'</option>';
                }else{
                    if(strlen($i) == $len) $options .= '<option value="'.$i.'">'.$v['catname'].'</option>';
                }
            }
	}
}else{
	foreach($CAT as $i=>$v)
	{
                if($idlen){
                    if((strlen($i) == $len) && (substr($i, 0, $idlen) == $id) && $id!=$i) $options .= '<option value="'.$i.'">'.$v['catname'].'</option>';
                }else{
                    if(strlen($i) == $len) $options .= '<option value="'.$i.'">'.$v['catname'].'</option>';
                }
	}
}

	if(empty($options)) exit;
	$str .= $options.'</select>';

}
echo $str;
?>