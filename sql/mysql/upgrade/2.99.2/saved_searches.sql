SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE `permission_dynamic_assignments` (
  `dynamic_condition_id` int(11) NOT NULL default '0',
  `permission_id` int(11) NOT NULL default '0',
  KEY `dynamic_conditiond_id` (`dynamic_condition_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `permission_dynamic_assignments_ibfk_2` FOREIGN KEY (`dynamic_condition_id`) REFERENCES `permission_dynamic_conditions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_dynamic_assignments_ibfk_3` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) TYPE=InnoDB;

CREATE TABLE `permission_dynamic_conditions` (
  `id` int(11) NOT NULL default '0',
  `permission_object_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `condition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `group_id` (`group_id`),
  KEY `condition_id` (`condition_id`),
  CONSTRAINT `permission_dynamic_conditions_ibfk_1` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_dynamic_conditions_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_dynamic_conditions_ibfk_3` FOREIGN KEY (`condition_id`) REFERENCES `saved_searches` (`id`) ON DELETE CASCADE
) TYPE=InnoDB;

CREATE TABLE `saved_searches` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `namespace` varchar(250) NOT NULL default '',
  `is_condition` tinyint(1) NOT NULL default '0',
  `is_complete` tinyint(1) NOT NULL default '0',
  `user_id` int(10) default NULL,
  `search` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `namespace` (`namespace`),
  KEY `is_condition` (`is_condition`),
  KEY `is_complete` (`is_complete`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `saved_searches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE=InnoDB;

CREATE TABLE `zseq_permission_dynamic_conditions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_saved_searches` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
SET FOREIGN_KEY_CHECKS=1;
