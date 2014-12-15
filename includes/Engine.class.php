<?php
require_once __DIR__ . "/iEngine.interface.php";

abstract class Engine implements iEngine
{
	/**
	 * @var string The URL of the request (Overwrite to rewrite it)
	 */
	public $url;
	public $fqdn;
	public $ident;
	/**
	 * @var string The method used for the request (e.g. GET or POST)
	 */
	public $method;
	/**
	 * @var Pini An instance of the Pini class providing configuration data for this engine
	 */
	protected $config;

	public function __construct(Pini $config)
	{
		$this->config = $config;
	}
}