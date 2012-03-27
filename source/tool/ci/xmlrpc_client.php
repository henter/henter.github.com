<?php

class xmlrpc_client
{
	private $url, $method, $output_options;
	
	function __construct($url, $method = 'POST')
	{
		$this->url = $url;
		$this->method = $method;
	}
	
	function request($method, $params, $output_options = array())
	{
		$request = xmlrpc_encode_request($method, $params, $output_options);
		$context = stream_context_create(array('http'=>array('method'=>$this->method, 'header'=>"Content-Type: text/xml", 'content'=>$request)));
		$data = @file_get_contents($this->url, false, $context);
		if(!$data)
		{
			$this->error = 'can not get webservice response';
			return false;
		}
		$response = xmlrpc_decode($data);
		if(is_array($response) && xmlrpc_is_fault($response))
		{
			$this->error = $response['faultString'];
			$this->errno = $response['faultCode'];
			return false;
		}
		return $response;
	}
}