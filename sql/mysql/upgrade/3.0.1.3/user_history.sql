SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `user_history` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_namespace` varchar(255) NOT NULL,
  `comments` text,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `action_namespace` (`action_namespace`),
  KEY `datetime` (`datetime`),
  CONSTRAINT `user_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) TYPE=InnoDB;

CREATE TABLE `zseq_user_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

ALTER TABLE `users` ADD COLUMN `last_login` datetime default NULL;
ALTER TABLE `users` ADD INDEX `last_login` (`last_login`);

SET FOREIGN_KEY_CHECKS=1;
