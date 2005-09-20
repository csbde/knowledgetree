ALTER TABLE `document_fields` ADD COLUMN `has_lookuptree` tinyint(1) default NULL;
ALTER TABLE `document_fields` ADD COLUMN `parent_fieldset` int(11) default NULL;
ALTER TABLE `metadata_lookup` ADD COLUMN `treeorg_parent` int(11) default NULL;
CREATE TABLE `document_fieldsets` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `namespace` char(255) NOT NULL default '',
  `mandatory` tinyint(4) NOT NULL default '0',
  `is_conditional` tinyint(1) NOT NULL default '0',
  `master_field` int(11) default NULL,
  UNIQUE KEY `id` (`id`)
) TYPE=InnoDB;

CREATE TABLE `document_type_fieldsets_link` (
  `id` int(11) NOT NULL default '0',
  `document_type_id` int(11) NOT NULL default '0',
  `fieldset_id` int(11) NOT NULL default '0',
  `is_mandatory` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=InnoDB;

CREATE TABLE `metadata_lookup_condition` (
  `id` int(11) NOT NULL default '0',
  `document_field_id` int(11) NOT NULL default '0',
  `metadata_lookup_id` int(11) NOT NULL default '0',
  `name` char(255) default NULL,
  UNIQUE KEY `id` (`id`)
) TYPE=InnoDB;

CREATE TABLE `metadata_lookup_condition_chain` (
  `id` int(11) NOT NULL default '0',
  `parent_condition` int(11) default NULL,
  `child_condition` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=InnoDB;

CREATE TABLE `metadata_lookup_tree` (
  `id` int(11) NOT NULL default '0',
  `document_field_id` int(11) NOT NULL default '0',
  `name` char(255) default NULL,
  `metadata_lookup_tree_parent` int(11) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `metadata_lookup_tree_parent` (`metadata_lookup_tree_parent`),
  KEY `document_field_id` (`document_field_id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_document_fieldsets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_metadata_lookup_condition` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_metadata_lookup_condition_chain` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `zseq_metadata_lookup_tree` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

