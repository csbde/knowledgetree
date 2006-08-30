CREATE TABLE `interceptor_instances` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `interceptor_namespace` varchar(255) NOT NULL,
  `config` text,
  PRIMARY KEY  (`id`),
  KEY `interceptor_namespace` (`interceptor_namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `zseq_interceptor_instances` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

