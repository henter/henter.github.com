<?php
/*
    DearCMS定制模板处理语法
*/

//处理模板
function dc_tpl_parse($str){
    
    $str = preg_replace ( "/\{post\s+([^}]+)\}/e", "dc_param_parse('\\1','post')", $str );
    $str = preg_replace ( "/\{\/post\}/", "<?php } unset(\$DATA); ?>", $str );
    
    $str = preg_replace ( "/\{arclist\s+([^}]+)\}/e", "dc_param_parse('\\1','arc')", $str );
    $str = preg_replace ( "/\{\/arclist\}/", "<?php } unset(\$DATA); ?>", $str );
    $str = preg_replace ( "/\{dzxthread\s+([^}]+)\}/e", "dc_param_parse('\\1','dzxthread')", $str );
    $str = preg_replace ( "/\{\/dzxthread\}/", "<?php } unset(\$DATA); ?>", $str );
    $str = preg_replace ( "/\{dzxgroup\s+([^}]+)\}/e", "dc_param_parse('\\1','dzxgroup')", $str );
    $str = preg_replace ( "/\{\/dzxgroup\}/", "<?php } unset(\$DATA); ?>", $str );
    
    $str = preg_replace ( "/\{postlist\s+([^}]+)\}/e", "dc_param_parse('\\1','post')", $str );
    $str = preg_replace ( "/\{\/postlist\}/", "<?php } unset(\$DATA); ?>", $str );
    $str = preg_replace ( "/\{reviewlist\s+([^}]+)\}/e", "dc_param_parse('\\1','review')", $str );
    $str = preg_replace ( "/\{\/reviewlist\}/", "<?php } unset(\$DATA); ?>", $str );
    $str = preg_replace ( "/\{walllist\s+([^}]+)\}/e", "dc_param_parse('\\1','wall')", $str );
    $str = preg_replace ( "/\{\/walllist\}/", "<?php } unset(\$DATA); ?>", $str );



    return $str;
}



//誓师信息
function postlist($paramstr=''){
    global $db;
    parse_str($paramstr);
    if($cachedata = _load_tpltag_cache('postlist',$paramstr,$cachetime)) return $cachedata;
    
    $order = $order ? trim($order) : 'DESC';
    $orderby = $orderby ? trim($orderby) : 'id';
    
    $num = $num ? intval($num) : 10;
    $where = ' WHERE 1=1 ';
    if($uid) $where .= " AND `uid`=".intval($uid);
    if($typeid) $where .= " AND `typeid`=".intval($typeid);
    if($elite) $where .= " AND `elite`= 1";
    if($order) $where .= " ORDER BY $orderby $order";

    
    $count = $db->result_first("SELECT count(*) FROM `post`");
    
    if(intval($offset) && $count>12) $offsetstr = intval($offset).',';

    $sql = "SELECT * FROM `post` $where LIMIT $offsetstr $num";
    $data = $db->select($sql);
    
    return _write_tpltag_cache('postlist',$paramstr,new_stripslashes($data),$cachetime);
}




//文章
function arclist($paramstr = ''){
    parse_str($paramstr);
    if($cachedata = _load_tpltag_cache('arclist',$paramstr,$cachetime)) return $cachedata;

    $data = wpapi_post($paramstr);
    return _write_tpltag_cache('arclist',$paramstr,$data,$cachetime);

}
