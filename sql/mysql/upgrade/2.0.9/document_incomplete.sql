CREATE TABLE `document_incomplete` (
  `id` int(10) unsigned NOT NULL default '0',
  `contents` tinyint(1) unsigned NOT NULL default '0',
  `metadata` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
