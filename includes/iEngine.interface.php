<?php
interface iEngine
{
	/**
	 * Process the URL rewriting.
	 *
	 * @return boolean true if the URL has been processed successfully and no other engine should be tried, false otherwise
	 */
	public function process();
}