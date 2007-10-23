CREATE TABLE `scheduler_tasks` (
  `id` int(11) NOT NULL default '0',
  `task` varchar(50) NOT NULL,
  `script_url` varchar(255) NOT NULL,
  `script_params` varchar(255),
  `is_complete` tinyint(4) NOT NULL default '0',
  `frequency` varchar(25),
  `run_time` datetime,
  `previous_run_time` datetime,
  `run_duration` float,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `task` (`task`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `zseq_scheduler_tasks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;