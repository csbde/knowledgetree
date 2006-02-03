SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `users` TYPE=InnoDB;
ALTER TABLE `users` ADD COLUMN `authentication_details` varchar(255) default NULL;
ALTER TABLE `users` ADD COLUMN `authentication_source_id` int(11) default NULL;
ALTER TABLE `users` ADD INDEX `authentication_source` (`authentication_source_id`);
ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`authentication_source_id`) REFERENCES `authentication_sources` (`id`) ON DELETE SET NULL;

CREATE TABLE `authentication_sources` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `namespace` varchar(255) NOT NULL default '',
  `authentication_provider` varchar(255) NOT NULL default '',
  `config` text NOT NULL,
  `is_user_source` tinyint(1) NOT NULL default '0',
  `is_group_source` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `namespace` (`namespace`)
) TYPE=InnoDB;

CREATE TABLE `zseq_authentication_sources` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

SET FOREIGN_KEY_CHECKS=1;
