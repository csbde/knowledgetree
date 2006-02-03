CREATE TABLE `plugins` (
  `id` int(11) NOT NULL default '0',
  `namespace` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `version` int(11) NOT NULL default '0',
  `disabled` tinyint(1) NOT NULL default '0',
  `data` text,
  PRIMARY KEY  (`id`),
  KEY `name` (`namespace`)
) TYPE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `zseq_plugins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM DEFAULT CHARSET=latin1;

