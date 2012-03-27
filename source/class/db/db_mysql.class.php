<?php
/**
 * Creat	Henter
 * Email	henter@henter.me
 * Modify	2010-12-23 23:26:49
 * FileName	db_mysql.class.php
 * Intro		改进自DiscuzX的数据类
 */


class db_mysql
{
	var $pre;
	var $version = '';
	var $querynum = 0;
	var $curlink;
	var $link = array();
	var $config = array();
	var $sqldebug = array();
	var $search = array('/union(\s*(\/\*.*\*\/)?\s*)+select/i', '/load_file(\s*(\/\*.*\*\/)?\s*)+\(/i', '/into(\s*(\/\*.*\*\/)?\s*)+outfile/i');
	var $replace = array('union &nbsp; select', 'load_file &nbsp; (', 'into &nbsp; outfile');
    
    
	function __construct($config = array()) {
		if(!empty($config)) {
			$this->set_config($config);
		}
	}

	function set_config($config) {
		$this->config = &$config;
		$this->dbname = $config['name'];
		$this->pre = $config['pre'];
	}
    
	function connect() {
		if(empty($this->config)) {
			$this->halt('config_db_not_found');
		}
		$this->curlink = $this->_dbconnect(
			$this->config['host'],
			$this->config['user'],
			$this->config['pw'],
			$this->config['charset'],
			$this->config['name'],
			$this->config['pconnect']
            );
            return $this->curlink;
	}
    
	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect) {
		$link = null;
		$func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
		if(!$link = @$func($dbhost, $dbuser, $dbpw, 1)) {
			$this->halt('notconnect');
		} else {
			$this->curlink = $link;
			if($this->version() > '4.1') {
				$dbcharset = $dbcharset ? $dbcharset : $this->config['dbcharset'];
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}
			$dbname && @mysql_select_db($dbname, $link);
		}
		return $link;
	}

	function table($tablename) {
		return $this->pre.$tablename;
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->curlink);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function fetch_object($query) {
		return mysql_fetch_object($query);
	}
    
	function get($sql, $keyfield = ''){
		$array = array();
		$result = $this->query($sql);
		while($r = $this->fetch_array($result))
		{
			if($keyfield)
			{
				$key = $r[$keyfield];
				$array[$key] = $r;
			}
			else
			{
				$array[] = $r;
			}
		}
		$this->free_result($result);
		return $array;
	}
    
	function get_row($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function get_var($sql) {
		return $this->result($this->query($sql), 0);
	}

	function get_col($sql) {
            $data = $this->get($sql);
		$new_array = array();
		// Extract the column values
		for ( $i = 0, $j = count( $data ); $i < $j; $i++ ) {
                    $value = array_values( $data[$i] );
			$new_array[$i] = $value[0];
		}
		return $new_array;
	}
       
/*
        function get_var2( $sql ){
            $data = $this->fetch_first( $sql );
            return $data[ @reset(@array_keys( $data )) ];
        }
*/
	/**
	 * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
	 *
	 * The following directives can be used in the query format string:
	 *   %d (decimal number)
	 *   %s (string)
	 *   %% (literal percentage sign - no argument needed)
	 *
	 * <code>
	 * wpdb::prepare( "SELECT * FROM `table` WHERE `column` = %s AND `field` = %d", 'foo', 1337 )
	 * wpdb::prepare( "SELECT DATE_FORMAT(`field`, '%%c') FROM `table` WHERE `column` = %s", 'foo' );
	 * </code>
	 */
	function prepare( $query = null ) { // ( $query, *$args )
		if ( is_null( $query ) )
			return;

		$args = func_get_args();
		array_shift( $args );
		// If args were passed as an array (as in vsprintf), move them up
		if ( isset( $args[0] ) && is_array($args[0]) )
			$args = $args[0];
		$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
		$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
		$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
		array_walk( $args, array( &$this, 'escape' ) );
		return @vsprintf( $query, $args );
	}
    
	function query($sql, $type = 'SILENT') {
		if(defined('DEBUG') && DC_DEBUG) {
			$starttime = dmicrotime();
		}
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->curlink))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->connect();
				return $this->query($sql, 'RETRY'.$type);
			}
			if ( preg_match( "/^\\s*(insert|replace) /i", $sql ) ) {
				$this->insert_id = $this->insert_id();
			}
			//if($type != 'SILENT' && substr($type, 5) != 'SILENT') {
				$this->halt('query_error', $sql);
			//}
		}
		if(defined('DEBUG') && DC_DEBUG) {
			$this->sqldebug[] = array($sql, number_format((dmicrotime() - $starttime), 6), debug_backtrace());
		}
		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->curlink);
	}

	function error() {
		return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
	}

	function errno() {
		return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
	}

	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}
       
	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
    
	function delete($table, $condition, $limit = 0, $unbuffered = true) {
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = $this->implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$sql = "DELETE FROM ".$table." WHERE $where ".($limit ? "LIMIT $limit" : '');
		return $this->query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	function insert($table, $data, $return_insert_id = true, $replace = false, $silent = false) {
		$sql = $this->implode_field_value($data);
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$silent = $silent ? 'SILENT' : '';
		$return = $this->query("$cmd $table SET $sql", $silent);
		return $return_insert_id ? $this->insert_id() : $return;
	}

	function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = $this->implode_field_value($data);
		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$where = '';
		if(empty($condition)) {
			$where = '1';
		} elseif(is_array($condition)) {
			$where = $this->implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$res = $this->query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}

	function tables(){
		$tables = array();
		$result = $this->query("SHOW TABLES");
		while($r = $this->fetch_array($result))
		{
			$tables[] = $r['Tables_in_'.$this->dbname];
		}
		$this->free_result($result);
		return $tables;
	}
    
	function table_exists($table){
		$tables = $this->tables($table);
		return in_array($table, $tables);
	}

	function field_exists($table, $field){
		$fields = $this->get_fields($table);
		return in_array($field, $fields);
	}

	function table_status($table){
		return $this->fetch_first("SHOW TABLE STATUS LIKE '$table'");
	}
    
	function get_primary($table){
		$result = $this->query("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_array($result))
		{
			if($r['Key'] == 'PRI') break;
		}
		$this->free_result($result);
		return $r['Field'];
	}

	function check_fields($tablename, $array){
		$fields = $this->get_fields($tablename);
		foreach($array AS $k=>$v)
		{
			if(!in_array($k,$fields))
			{
				$this->halt('MySQL Query Error', "Unknown column '$k' in field list");
				return false;
			}
		}
	}

	function get_fields($table){
		$fields = array();
		$result = $this->query("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_array($result))
		{
			$fields[] = $r['Field'];
		}
		$this->free_result($result);
		return $fields;
	}

	function implode_field_value($array, $glue = ',') {
		$sql = $comma = '';
		foreach ($array as $k => $v) {
			$sql .= $comma."`$k`='$v'";
			$comma = $glue;
		}
		return $sql;
	}
	function escape($string){
		if(!is_array($string)) return str_replace(array('\n', '\r'), array(chr(10), chr(13)), mysql_real_escape_string(preg_replace($this->search, $this->replace, $string), $this->curlink));
		foreach($string as $key=>$val) $string[$key] = $this->escape($val);
		return $string;
	}
    
	function version() {
		if(empty($this->version)) {
			$this->version = mysql_get_server_info($this->curlink);
		}
		return $this->version;
	}

	function close() {
		return mysql_close($this->curlink);
	}

	function halt($message = '', $sql = '') {
		global $_G;
		$dberror = $this->error();
		$dberrno = $this->errno();
		$phperror = '<table style="font-size:12px" cellpadding="0"><tr><td width="270">File</td><td width="80">Line</td><td>Function</td></tr>';
		foreach (debug_backtrace() as $error) {
			$error['file'] = str_replace(DCS, '', $error['file']);
			$error['class'] = isset($error['class']) ? $error['class'] : '';
			$error['type'] = isset($error['type']) ? $error['type'] : '';
			$error['function'] = isset($error['function']) ? $error['function'] : '';
			$phperror .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
		}
		$phperror .= '</table>';
		@header('Content-Type: text/html; charset='.$_G['config']['output']['charset']);
		echo '<div style="position:absolute;border:1px #C00 solid;font-size:12px;background:#FFEBE8;padding:0.5em;line-height:1.5em">';
		echo $message.'<br />';
		echo $dberror.'<br />';
		echo $sql ? $sql.'<br />' : '';
		echo $dberrno ? $dberrno.'<br />' : '';
		echo "<b>PHP Backtrace</b><br />$phperror<br /></div>";
		exit();
	}

}
