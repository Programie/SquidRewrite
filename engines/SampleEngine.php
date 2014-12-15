<?php
class SampleEngine extends Engine
{
	public function process()
	{
		// We do not want to rewrite URLs not requested via GET
		if ($this->method != "GET")
		{
			return false;
		}

		if (!preg_match("|^http://example.com/(.*)$|", $this->url, $matches))
		{
			return false;
		}

		$this->url = "http://rewritten.example.com/" . $matches[1];

		return true;
	}
}