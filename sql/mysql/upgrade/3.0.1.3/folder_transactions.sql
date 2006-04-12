SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE `folder_transactions` (
  `id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` char(30) default NULL,
  `comment` char(255) NOT NULL default '',
  `transaction_namespace` char(255) NOT NULL default 'ktcore.transactions.event',
  UNIQUE KEY `id` (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `folder_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folder_transactions_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE
) TYPE=InnoDB;

CREATE TABLE `zseq_folder_transactions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET FOREIGN_KEY_CHECKS=1;
