CREATE TABLE `help_replacement` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `zseq_help_replacement` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;
