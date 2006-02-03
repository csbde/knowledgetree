SET FOREIGN_KEY_CHECKS=0;


CREATE TABLE `dashlet_disables` (
  `id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `dashlet_namespace` varchar(255) NOT NULL default '',
  UNIQUE KEY `id` (`id`),
  INDEX (`user_id`),
  INDEX (`dashlet_namespace`)
) TYPE=InnoDB ;


CREATE TABLE `zseq_dashlet_disables` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;


SET FOREIGN_KEY_CHECKS=1;
