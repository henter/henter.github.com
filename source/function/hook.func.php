<?php
/**
 * 添加filter函数到某hook
 * $tag hook标识 
 * $function_to_add 指定的函数名 
 * $priority 优先级 
 * $accepted_args 接受的参数数量
 */
function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
	global $wp_filter, $merged_filters;

	$idx = _wp_filter_build_unique_id($tag, $function_to_add, $priority);
	$wp_filter[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
	unset( $merged_filters[ $tag ] );
	return true;
}

/**
 * 检查某hook是否已有某filter
 */
function has_filter($tag, $function_to_check = false) {
	global $wp_filter;

	$has = !empty($wp_filter[$tag]);
	if ( false === $function_to_check || false == $has )
		return $has;

	if ( !$idx = _wp_filter_build_unique_id($tag, $function_to_check, false) )
		return false;

	foreach ( (array) array_keys($wp_filter[$tag]) as $priority ) {
		if ( isset($wp_filter[$tag][$priority][$idx]) )
			return $priority;
	}

	return false;
}

/**
 * 执行某filter hook相关联的所有函数.
 */
function apply_filters($tag, $value) {
	global $wp_filter, $merged_filters, $wp_current_filter;

	$args = array();
	$wp_current_filter[] = $tag;

	if ( !isset($wp_filter[$tag]) ) {
		array_pop($wp_current_filter);
		return $value;
	}

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	if ( empty($args) )
		$args = func_get_args();

	do {
		foreach( (array) current($wp_filter[$tag]) as $the_ )
			if ( !is_null($the_['function']) ){
				$args[1] = $value;
				$value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
			}

	} while ( next($wp_filter[$tag]) !== false );

	array_pop( $wp_current_filter );

	return $value;
}

/**
 * 执行某filter hook相关联的所有函数. 参数为数组
 */
function apply_filters_ref_array($tag, $args) {
	global $wp_filter, $merged_filters, $wp_current_filter;

	$wp_current_filter[] = $tag;

	if ( !isset($wp_filter[$tag]) ) {
		array_pop($wp_current_filter);
		return $args[0];
	}

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	do {
		foreach( (array) current($wp_filter[$tag]) as $the_ )
			if ( !is_null($the_['function']) )
				$args[0] = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

	} while ( next($wp_filter[$tag]) !== false );

	array_pop( $wp_current_filter );

	return $args[0];
}

/**
 * 去掉某hook的filter，所有参数必须与添加filter时一致
 * 可用时用于action和filter
 */
function remove_filter($tag, $function_to_remove, $priority = 10, $accepted_args = 1) {
	$function_to_remove = _wp_filter_build_unique_id($tag, $function_to_remove, $priority);

	$r = isset($GLOBALS['wp_filter'][$tag][$priority][$function_to_remove]);

	if ( true === $r) {
		unset($GLOBALS['wp_filter'][$tag][$priority][$function_to_remove]);
		if ( empty($GLOBALS['wp_filter'][$tag][$priority]) )
			unset($GLOBALS['wp_filter'][$tag][$priority]);
		unset($GLOBALS['merged_filters'][$tag]);
	}

	return $r;
}

/**
 * 去掉所有filter
 */
function remove_all_filters($tag, $priority = false) {
	global $wp_filter, $merged_filters;

	if( isset($wp_filter[$tag]) ) {
		if( false !== $priority && isset($wp_filter[$tag][$priority]) )
			unset($wp_filter[$tag][$priority]);
		else
			unset($wp_filter[$tag]);
	}

	if( isset($merged_filters[$tag]) )
		unset($merged_filters[$tag]);

	return true;
}

/**
 * 获取当前的 filter 或 action.
 */
function current_filter() {
	global $wp_current_filter;
	return end( $wp_current_filter );
}


/**
 * 添加action函数到某hook
 */
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
	return add_filter($tag, $function_to_add, $priority, $accepted_args);
}


/**
 * 执行某action hook相关联的所有action.
 */
function do_action($tag, $arg = '') {
	global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;

	if ( ! isset($wp_actions) )
		$wp_actions = array();

	if ( ! isset($wp_actions[$tag]) )
		$wp_actions[$tag] = 1;
	else
		++$wp_actions[$tag];

	$wp_current_filter[] = $tag;

	if ( !isset($wp_filter[$tag]) ) {
		array_pop($wp_current_filter);
		return;
	}

	$args = array();
	if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
		$args[] =& $arg[0];
	else
		$args[] = $arg;
	for ( $a = 2; $a < func_num_args(); $a++ )
		$args[] = func_get_arg($a);

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	do {
		foreach ( (array) current($wp_filter[$tag]) as $the_ )
			if ( !is_null($the_['function']) )
				call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

	} while ( next($wp_filter[$tag]) !== false );

	array_pop($wp_current_filter);
}

/**
 * 获取某action hook的执行次数.
 */
function did_action($tag) {
	global $wp_actions;

	if ( ! isset( $wp_actions ) || ! isset( $wp_actions[$tag] ) )
		return 0;

	return $wp_actions[$tag];
}

/**
 * 执行某action hook相关联的所有action.
 * 参数为数组
 */
function do_action_ref_array($tag, $args) {
	global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;

	if ( ! isset($wp_actions) )
		$wp_actions = array();

	if ( ! isset($wp_actions[$tag]) )
		$wp_actions[$tag] = 1;
	else
		++$wp_actions[$tag];

	$wp_current_filter[] = $tag;

	if ( !isset($wp_filter[$tag]) ) {
		array_pop($wp_current_filter);
		return;
	}

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	do {
		foreach( (array) current($wp_filter[$tag]) as $the_ )
			if ( !is_null($the_['function']) )
				call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

	} while ( next($wp_filter[$tag]) !== false );

	array_pop($wp_current_filter);
}

/**
 * 检查某hook是否已有某action
 */
function has_action($tag, $function_to_check = false) {
	return has_filter($tag, $function_to_check);
}

/**
 * 去掉某hook下的某action
 */
function remove_action($tag, $function_to_remove, $priority = 10, $accepted_args = 1) {
	return remove_filter($tag, $function_to_remove, $priority, $accepted_args);
}

/**
 * 去掉某hook下的所有action.
 */
function remove_all_actions($tag, $priority = false) {
	return remove_all_filters($tag, $priority);
}

/**
 * 获取唯一id 作为数组key保存
 */
function _wp_filter_build_unique_id($tag, $function, $priority) {
	global $wp_filter;

	if ( is_string($function) )
		$return  =  $function;

	if ( is_object($function) ) {
		// Closures are currently implemented as objects
		$function = array( $function, '' );
	} else {
		$function = (array) $function;
	}
    
	if (is_object($function[0]) ) {
		// Object Class Calling
		if ( function_exists('spl_object_hash') ) {
			$return  = spl_object_hash($function[0]) . $function[1];
		} else {
			$return  =  get_class($function[0]).$function[1];
		}
	} else if ( is_string($function[0]) ) {
		// Static Calling
		$return  =  $function[0].$function[1];
	}
    return $tag.$return.$priority;
}

?>
