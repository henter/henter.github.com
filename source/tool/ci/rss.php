<?php

class rss
{
	public $rssHeader,
	       $rssChannel,
	       $rssImage,
	       $rssItem;

	function __construct($xml = '1.0', $rss = '2.0', $encoding = 'utf-8')
	{
		$this->header($xml, $rss, $encoding);
	}

	function header($xml = '1.0', $rss = '2.0', $encoding = 'utf-8')
	{
		$this->rssHeader  = "<?xml version=\"$xml\" encoding=\"$encoding\"?>\n";
		$this->rssHeader .= "<rss version=\"$rss\">\n";
	}

	function channel($channel)
	{
		$this->rssChannel  = "<channel>\n";
		foreach($channel as $key => $value)
		{
			$this->rssChannel .=" <$key><![CDATA[".$value."]]></$key>\n";
		}
	}

	function image($image)
	{
		$this->rssImage  = "  <image>\n";
		foreach($image as $key => $value)
		{
			$this->rssImage .=" <$key><![CDATA[".$value."]]></$key>\n";
		}
		$this->rssImage .= "  </image>\n";
	}

	function item($item)
	{
		$this->rssItem .= "<item>\n";
		foreach($item as $key => $value)
		{
			$this->rssItem .=" <$key><![CDATA[".$value."]]></$key>\n";
		}
		$this->rssItem .= "</item>\n";
	}

	function footer()
	{
		$data = $this->rssHeader;
		$data .= $this->rssChannel;
		$data .= $this->rssImage;
		$data .= $this->rssItem;
		$data .= "</channel></rss>";
		return $data;
	}
}
?>