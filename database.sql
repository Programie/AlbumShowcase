CREATE TABLE `albums` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `releaseDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE `downloads` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`albumId` int(11) NOT NULL,
	`date` datetime NOT NULL,
	`ipAddress` varchar(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `albumId` (`albumId`),
	CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`id`) REFERENCES `albums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tracks` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`albumId` int(11) NOT NULL,
	`artist` varchar(200) NOT NULL DEFAULT '',
	`title` varchar(200) NOT NULL DEFAULT '',
	`length` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `albumId` (`albumId`),
	CONSTRAINT `tracks_ibfk_1` FOREIGN KEY (`id`) REFERENCES `albums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;