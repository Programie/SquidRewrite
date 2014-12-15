<?php
require_once __DIR__ . "/../includes/Engine.class.php";
require_once __DIR__ . "/../vendor/autoload.php";

class Rewriter
{
	/**
	 * @var Pini
	 */
	private $config;
	/**
	 * @var array
	 */
	private $engines;
	/**
	 * @var string
	 */
	private $logFile;

	private function loadConfig()
	{
		$this->config = new Pini(__DIR__ . "/../config/config.ini");

		$logSection = $this->config->getSection("log");
		if ($logSection)
		{
			$pathProperty = $logSection->getProperty("path");
			if ($pathProperty)
			{
				$this->logFile = $pathProperty->value;
			}
		}
	}

	private function loadEngines()
	{
		$this->engines = array();

		$enginesSection = $this->config->getSection("engines");
		if (!$enginesSection)
		{
			return;
		}

		$engineProperty = $enginesSection->getProperty("engine");
		if (!$engineProperty)
		{
			return;
		}

		foreach ($engineProperty->value as $engine)
		{
			$this->writeLog("Loading engine: " . $engine);

			$file = __DIR__ . "/../engines/" . $engine . ".php";

			if (!file_exists($file))
			{
				$this->writeLog("No such file or directory: " . $file);
				continue;
			}

			require_once $file;

			if (!class_exists($engine))
			{
				$this->writeLog("Class not found: " . $engine);
				continue;
			}

			$config = new Pini(__DIR__ . "/../config/engines/" . $engine . ".ini");

			$this->engines[$engine] = new $engine($config);
		}
	}

	public function writeLog($string)
	{
		if (!$this->logFile)
		{
			return;
		}

		file_put_contents($this->logFile, "[" . date("Y-m-d H:i:s") . "] " . $string . "\n", FILE_APPEND);
	}

	public function reload()
	{
		$this->writeLog("Reloading configuration and engines");

		$this->loadConfig();
		$this->loadEngines();
	}

	public function processLine($line)
	{
		$line = trim($line);

		list($url, $fqdn, $ident, $method) = explode(" ", $line);

		$this->writeLog("Input: url = " . $url . ", fqdn = " . $fqdn . ", ident = " . $ident . ", method = " . $method);

		/**
		 * @var $engine Engine
		 */
		foreach ($this->engines as $engineName => $engine)
		{
			$engine->url = $url;
			$engine->fqdn = $fqdn;
			$engine->ident = $ident;
			$engine->method = $method;

			$this->writeLog("Processing using " . $engineName);

			// Process the URL using this engine (returns true on success or false if another engine should be tried)
			if ($engine->process())
			{
				$url = $engine->url;
				$fqdn = $engine->fqdn;
				$ident = $engine->ident;
				$method = $engine->method;

				$this->writeLog("Successfully processed");
				break;
			}
		}

		$response = array
		(
			$url,
			$fqdn,
			$ident,
			$method
		);

		$this->writeLog("Output: url = " . $url . ", fqdn = " . $fqdn . ", ident = " . $ident . ", method = " . $method);

		return implode(" ", $response);
	}
}