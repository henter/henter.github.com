<?php
/*
    文章用到的函数
*/

//通过tag数值，获取tag数组
function article_parse_tags($tag) {
	$tag = intval($tag);
	$article_tags = array();
	for($i=1; $i<=8; $i++) {
		$k = pow(2, $i-1);
		$article_tags[$i] = ($tag & $k) ? 1 : 0;
	}
	return $article_tags;
}
//通过tag数组得出tag数值保存到数据库
function article_make_tag($tags) {
	$tags = (array)$tags;
	$tag = 0;
	for($i=1; $i<=8; $i++) {
		if(!empty($tags[$i])) {
			$tag += pow(2, $i-1);
		}
	}
	return $tag;
}