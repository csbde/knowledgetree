ALTER TABLE `documents` ADD COLUMN `permission_object_id` int(11) default NULL;
ALTER TABLE `documents` ADD COLUMN `permission_lookup_id` int(11) default NULL;
ALTER TABLE `documents` ADD INDEX `permission_object_id` (`permission_object_id`);
ALTER TABLE `documents` ADD INDEX `permission_lookup_id` (`permission_lookup_id`);
ALTER TABLE `folders` ADD COLUMN `permission_object_id` int(11) default NULL;
ALTER TABLE `folders` ADD COLUMN `permission_lookup_id` int(11) default NULL;
ALTER TABLE `folders` ADD INDEX `permission_object_id` (`permission_object_id`);
ALTER TABLE `folders` ADD INDEX `permission_lookup_id` (`permission_lookup_id`);
ALTER TABLE `upgrades` CHANGE COLUMN `parent` `parent` char(40) default NULL; # was char(100) default NULL

CREATE TABLE `permission_assignments` (
  `id` int(11) NOT NULL default '0',
  `permission_id` int(11) NOT NULL default '0',
  `permission_object_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `permission_and_object` (`permission_id`,`permission_object_id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_object_id` (`permission_object_id`)
) TYPE=InnoDB;

CREATE TABLE `permission_descriptor_groups` (
  `descriptor_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  UNIQUE KEY `descriptor_id` (`descriptor_id`,`group_id`),
  KEY `descriptor_id_2` (`descriptor_id`),
  KEY `group_id` (`group_id`)
) TYPE=InnoDB;

CREATE TABLE `permission_descriptors` (
  `id` int(11) NOT NULL default '0',
  `descriptor` varchar(32) NOT NULL default '',
  `descriptor_text` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `descriptor_2` (`descriptor`),
  KEY `descriptor` (`descriptor`)
) TYPE=InnoDB;

CREATE TABLE `permission_lookup_assignments` (
  `id` int(11) NOT NULL default '0',
  `permission_id` int(11) NOT NULL default '0',
  `permission_lookup_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `permission_and_lookup` (`permission_id`,`permission_lookup_id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_lookup_id` (`permission_lookup_id`)
) TYPE=InnoDB;

CREATE TABLE `permission_lookups` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `permission_objects` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `built_in` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=InnoDB;

CREATE TABLE `zseq_permission_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_permission_descriptors` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_permission_lookup_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_permission_lookups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_permission_objects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_permissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

-- ALTER TABLE `folders` DROP COLUMN `inherit_parent_folder_permission`; # was int(11) default NULL
-- ALTER TABLE `folders` DROP INDEX `permission_folder_id`; # was INDEX (`permission_folder_id`)
-- ALTER TABLE `folders` DROP COLUMN `permission_folder_id`; # was int(11) default NULL

INSERT INTO `permissions` VALUES (1, 'ktcore.permissions.read', 'Core: Read', 1);
INSERT INTO `permissions` VALUES (2, 'ktcore.permissions.write', 'Core: Write', 1);
INSERT INTO `permissions` VALUES (3, 'ktcore.permissions.addFolder', 'Core: Add Folder', 1);
INSERT INTO `zseq_permissions` VALUES (3);
