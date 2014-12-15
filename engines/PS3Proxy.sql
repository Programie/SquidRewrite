CREATE TABLE `groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `files` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`groupId` int(10) unsigned DEFAULT NULL,
	`url` varchar(255) NOT NULL DEFAULT '',
	`name` varchar(200) DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `groupId` (`groupId`),
	CONSTRAINT `files_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;