CREATE TABLE `plugin_helper` (
	`id` INT NOT NULL default '0',
	`namespace` VARCHAR(120) NOT NULL,
	`plugin` VARCHAR(120) NOT NULL,
	`classname` VARCHAR(120),
	`pathname` VARCHAR(255),
	`object` VARCHAR(1000) NOT NULL,
	`classtype` VARCHAR(120) NOT NULL,
	`viewtype` ENUM('general', 'dashboard', 'plugin', 'folder', 'document', 'admindispatcher', 'dispatcher') NOT NULL default 'general',
    PRIMARY KEY  (`id`),
    KEY `name` (`namespace`),
    KEY `parent` (`plugin`),
    KEY `view` (`viewtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `zseq_plugin_helper` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
