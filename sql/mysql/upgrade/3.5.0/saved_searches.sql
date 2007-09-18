CREATE TABLE `search_saved` (
	`id` int(11) NOT NULL,
	`name` varchar(100) NOT NULL,
	`expression` tinytext NOT NULL,
	`user_id` int(11) NOT NULL,
	`type` enum('S','C','W','B') NOT NULL default 'S' COMMENT 'S=saved search, C=permission, w=workflow, B=subscription',
	`shared` tinyint(4) NOT NULL default '0',
	PRIMARY KEY  (`id`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
	      
CREATE TABLE `zseq_search_saved` (
	`id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `search_saved_events` (
	`document_id` int(11) NOT NULL
) ENGINE=innodb DEFAULT CHARSET=utf8  ;

