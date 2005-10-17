SET FOREIGN_KEY_CHECKS=0;

DROP TABLE `document_fieldsets`;
DROP TABLE `zseq_document_fieldsets`;

ALTER TABLE `document_type_fieldsets_link` DROP COLUMN `is_mandatory`; # was tinyint(1) NOT NULL default '0'
ALTER TABLE `folders` ADD COLUMN `restrict_document_types` tinyint(1) NOT NULL default '0';

CREATE TABLE `field_behaviour_options` (
  `behaviour_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `instance_id` int(11) NOT NULL default '0',
  KEY `behaviour_id` (`behaviour_id`),
  KEY `field_id` (`field_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `field_behaviour_options_ibfk_1` FOREIGN KEY (`behaviour_id`) REFERENCES `field_behaviours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_behaviour_options_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_behaviour_options_ibfk_3` FOREIGN KEY (`instance_id`) REFERENCES `field_value_instances` (`id`) ON DELETE CASCADE
) TYPE=InnoDB;

CREATE TABLE `field_behaviours` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `field_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`),
  KEY `name` (`name`),
  CONSTRAINT `field_behaviours_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`)
) TYPE=InnoDB;

CREATE TABLE `field_orders` (
  `parent_field_id` int(11) NOT NULL default '0',
  `child_field_id` int(11) NOT NULL default '0',
  `fieldset_id` int(11) NOT NULL default '0',
  UNIQUE KEY `child_field` (`child_field_id`),
  KEY `parent_field` (`parent_field_id`),
  KEY `fieldset_id` (`fieldset_id`)
) TYPE=InnoDB;

CREATE TABLE `field_value_instances` (
  `id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `field_value_id` int(11) NOT NULL default '0',
  `behaviour_id` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`),
  KEY `field_value_id` (`field_value_id`),
  KEY `behaviour_id` (`behaviour_id`)
) TYPE=InnoDB;

CREATE TABLE `fieldsets` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `namespace` char(255) NOT NULL default '',
  `mandatory` tinyint(4) NOT NULL default '0',
  `is_conditional` tinyint(1) NOT NULL default '0',
  `master_field` int(11) default NULL,
  `is_generic` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `is_generic` (`is_generic`)
) TYPE=InnoDB;

CREATE TABLE `zseq_document_type_fieldsets_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_field_behaviours` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_field_value_instances` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_fieldsets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

ALTER TABLE `document_fields` ADD INDEX `parent_fieldset` (`parent_fieldset`);
ALTER TABLE `document_fields` ADD CONSTRAINT `document_fields_ibfk_1` FOREIGN KEY (`parent_fieldset`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE;
ALTER TABLE `fieldsets` ADD COLUMN `is_complex` tinyint(1) NOT NULL default '0';
ALTER TABLE `fieldsets` ADD COLUMN `is_complete` tinyint(1) NOT NULL default '1';
ALTER TABLE `fieldsets` ADD INDEX `is_complete` (`is_complete`);

SET FOREIGN_KEY_CHECKS=1;
