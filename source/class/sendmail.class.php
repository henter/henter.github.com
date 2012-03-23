<?php
class sendmail
{
	function sendmail()
	{
		$this->__construct();
	}

	function __construct()
    {
		global $DC;
		$this->set($DC['mail_server'], $DC['mail_port'], $DC['mail_user'], $DC['mail_password'], $DC['mail_type']);
		$this->auth = 1;
	}

	function set($server, $port, $user, $password, $type = 1, $delimiter = 1, $mailusername = 0)
	{
		$this->type = $type;
		$this->server = $server;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
        $this->delimiter = $delimiter == 1 ? "\r\n" : ($delimiter == 2 ? "\r" : "\n");
		$this->mailusername = $mailusername;
	}
	
	function send($email_to, $email_subject, $email_message, $email_from = '')
	{
		global $DC;
		$email_subject = '=?'.CHARSET.'?B?'.base64_encode(str_replace("\r", '', $email_subject)).'?=';
		$email_message = str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $email_message)))));
		$adminemail = $this->type == 1 ? $DC['mail_user'] : $DC['adminemail'];
		$email_from = $email_from == '' ? '=?'.CHARSET.'?B?'.base64_encode($DC['sitename'])."?= <$adminemail>" : (preg_match('/^(.+?) \<(.+?)\>$/',$email_from, $from) ? '=?'.CHARSET.'?B?'.base64_encode($from[1])."?= <$from[2]>" : $email_from);
		$emails = explode(',', $email_to);
		foreach($emails as $touser)
		{
			$tousers[] = preg_match('/^(.+?) \<(.+?)\>$/',$touser, $to) ? ($this->mailusername ? '=?'.CHARSET.'?B?'.base64_encode($to[1])."?= <$to[2]>" : $to[2]) : $touser;
		}
		$email_to = implode(',', $tousers);
		$headers = "From: $email_from{$this->delimiter}X-Priority: 3{$this->delimiter}X-Mailer: Phpcms {$this->delimiter}MIME-Version: 1.0{$this->delimiter}Content-type: text/html; charset=".CHARSET."{$this->delimiter}";
		if($this->type == 1)
		{
			return $this->smtp($email_to, $email_subject, $email_message, $email_from, $headers);
		}
		elseif($this->type == 2)
		{
			return @mail($email_to, $email_subject, $email_message, $headers);
		}
		else
		{
			ini_set('SMTP', $this->server);
			ini_set('smtp_port', $this->port);
			ini_set('sendmail_from', $email_from);
			return @mail($email_to, $email_subject, $email_message, $headers);
		}
	}

	function smtp($email_to, $email_subject, $email_message, $email_from = '', $headers = '')
	{
		if(!$fp = fsockopen($this->server, $this->port, $errno, $errstr, 10))
		{
			$this->errorlog('SMTP', "($this->server:$this->port) CONNECT - Unable to connect to the SMTP server", 0);
			return false;
		}
		stream_set_blocking($fp, true);
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != '220')
		{
			$this->errorlog('SMTP', "$this->server:$this->port CONNECT - $lastmessage", 0);
			return false;
		}
		fputs($fp, "EHLO Phpcms\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250)
		{
			$this->errorlog('SMTP', "($this->server:$this->port) HELO/EHLO - $lastmessage", 0);
			return false;
		}
		while(1)
		{
			if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage))
			{
				break;
			}
			$lastmessage = fgets($fp, 512);
		}
		fputs($fp, "AUTH LOGIN\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334)
		{
			$this->errorlog('SMTP', "($this->server:$this->port) AUTH LOGIN - $lastmessage", 0);
			return false;
		}
		fputs($fp, base64_encode($this->user)."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334)
		{
			$this->errorlog('SMTP', "($this->server:$this->port) USERNAME - $lastmessage", 0);
			return false;
		}
		fputs($fp, base64_encode($this->password)."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 235)
		{
			$this->errorlog('SMTP', "($this->server:$this->port) PASSWORD - $lastmessage", 0);
			return false;
		}
		fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250)
		{
			fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 250)
			{
				$this->errorlog('SMTP', "($this->server:$this->port) MAIL FROM - $lastmessage", 0);
				return false;
			}
		}
		$email_tos = array();
		$emails = explode(',', $email_to);
		foreach($emails as $touser)
		{
			$touser = trim($touser);
			if($touser) 
			{
				fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
				$lastmessage = fgets($fp, 512);
				if(substr($lastmessage, 0, 3) != 250)
				{
					fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
					$lastmessage = fgets($fp, 512);
					$this->errorlog('SMTP', "($this->server:$this->port) RCPT TO - $lastmessage", 0);
					return false;
				}
			}
		}
		fputs($fp, "DATA\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 354)
		{
			$this->errorlog('SMTP', "($this->server:$this->port) DATA - $lastmessage", 0);
		}
		$headers .= 'Message-ID: <'.gmdate('YmdHs').'.'.substr(md5($email_message.microtime()), 0, 6).rand(100000, 999999).'@'.$_SERVER['HTTP_HOST'].">{$this->delimiter}";
		fputs($fp, "Date: ".gmdate('r')."\r\n");
		fputs($fp, "To: ".$email_to."\r\n");
		fputs($fp, "Subject: ".$email_subject."\r\n");
		fputs($fp, $headers."\r\n");
		fputs($fp, "\r\n\r\n");
		fputs($fp, "$email_message\r\n.\r\n");
		fputs($fp, "QUIT\r\n");
		return true;
	}

	function errorlog($type, $message, $is)
	{
		$this->error[] = array($type, $message, $is);
	}
}
?>