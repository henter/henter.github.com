<?php

class xmlrpc_server
{
	private $xmlrpc_server;
	
	function __construct()
	{
		$this->xmlrpc_server = xmlrpc_server_create();
	}
	
	function register($method, $function)
	{
		xmlrpc_server_register_method($this->xmlrpc_server, $method, $function);
	}
	
	function call($output_options = null)
	{
		if (!isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = file_get_contents('php://input');
		$HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
		$response = xmlrpc_server_call_method($this->xmlrpc_server, $HTTP_RAW_POST_DATA, $output_options);
		file_put_contents('data.txt', $HTTP_RAW_POST_DATA);
		header('Content-Type: text/xml');
		echo $response;
	}
	
	function __destruct()
	{
		xmlrpc_server_destroy($this->xmlrpc_server);
	}
}