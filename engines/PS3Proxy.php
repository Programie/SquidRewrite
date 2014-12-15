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

		$databaseSection = $config->getSection("database");
		if ($databaseSection)
		{
			$dsn = $databaseSection->getProperty("dsn");
			$username = $databaseSection->getProperty("username");
			$password = $databaseSection->getProperty("password");

			if ($dsn and $username and $password)
			{
				$this->pdo = new PDO($dsn->value, $username->value, $password->value);
				$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
				$this->pdo->query("SET NAMES utf8");
			}
		}

		$pathsSection = $config->getSection("paths");
		if ($pathsSection)
		{
			$pkgProperty = $pathsSection->getProperty("pkg");
			if ($pkgProperty)
			{
				$this->pkgPath = $pkgProperty->value;
			}

			$localUrlProperty = $pathsSection->getProperty("localUrl");
			if ($localUrlProperty)
			{
				$this->localUrl = $localUrlProperty->value;
			}
		}
	}

	public function process()
	{
		if (!preg_match("|^http://(.*)\.dl\.playstation\.net/cdn/(.*)/(.*)/(.*)\.pkg|", $this->url, $matches))
		{
			return false;
		}

		$file = $matches[4] . ".pkg";

		if (file_exists($this->pkgPath . "/" . $file))
		{
			$this->url = $this->localUrl . "/" . $file;
		}
		else
		{
			if ($this->pdo)
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
		}

		return true;
	}
}