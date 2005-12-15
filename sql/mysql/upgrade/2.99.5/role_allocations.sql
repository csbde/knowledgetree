SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `role_allocations` (
  `id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `role_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB ;

CREATE TABLE `zseq_role_allocations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;


SET FOREIGN_KEY_CHECKS=1;
