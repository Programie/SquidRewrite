<?php
class PS3Proxy extends Engine
{
	/**
	 * @var PDO
	 */
	private $pdo;
	/**
	 * @var string
	 */
	private $pkgPath;
	/**
	 * @var string
	 */
	private $localUrl;

	public function __construct(Pini $config)
	{
		parent::__construct($config);

		$this->pdo = new PDO($config->getValue("database", "dsn"), $config->getValue("database", "username"), $config->getValue("database", "password"));
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		$this->pdo->query("SET NAMES utf8");

		$this->pkgPath = $config->getValue("paths", "pkg");
		$this->localUrl = $config->getValue("paths", "localUrl");
	}

	public function process()
	{
		if (!preg_match("|^http://zeus\.dl\.playstation\.net/cdn/(.*)/(.*)/(.*)\.pkg|", $this->url, $matches))
		{
			return false;
		}

		$file = $matches[3] . ".pkg";

		if (file_exists($this->pkgPath . "/" . $file))
		{
			$this->url = $this->localUrl . "/" . $file;
		}
		else
		{
			$query = $this->pdo->prepare("SELECT `id` FROM `files` WHERE `url` = :url");

			$query->execute(array
			(
				":url" => $this->url
			));

			if (!$query->rowCount())
			{
				$query = $this->pdo->prepare("
					INSERT INTO `files`
					SET `url` = :url
				");

				$query->execute(array
				(
					":url" => $this->url
				));
			}
		}

		return true;
	}
}