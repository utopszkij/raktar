
DROP TABLE IF EXISTS `dbverzio`;
CREATE TABLE `dbverzio` (
  `verzio` varchar(32) COLLATE utf8mb3_hungarian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;


DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent` int DEFAULT NULL,
  `name` varchar(80) COLLATE utf8mb3_hungarian_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `group_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(80) COLLATE utf8mb3_hungarian_ci DEFAULT '',
  `password` varchar(80) COLLATE utf8mb3_hungarian_ci DEFAULT '',
  `realname` varchar(80) COLLATE utf8mb3_hungarian_ci DEFAULT '',
  `email` varchar(80) COLLATE utf8mb3_hungarian_ci DEFAULT '',
  `avatar` varchar(128) COLLATE utf8mb3_hungarian_ci DEFAULT '',
  `email_verifyed` tinyint DEFAULT '0',
  `enabled` int DEFAULT '1',
  `deleted` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
