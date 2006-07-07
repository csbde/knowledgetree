CREATE TABLE `column_entries` (
    `id`    int(11) not null default 0,
    PRIMARY KEY (`id`),
	`column_namespace` varchar(255) not null default '', 
	`view_namespace` varchar(255) not null default '',
	`config_array` text not null default '',
	`position` int(11) not null default 0,
	`required` tinyint(1) not null default 0,
	INDEX (`view_namespace`)
) Type=InnoDB;

-- browse 
INSERT INTO `column_entries` VALUES (1,'ktcore.columns.selection','ktcore.views.browse','',0,1);
INSERT INTO `column_entries` VALUES (2,'ktcore.columns.title','ktcore.views.browse','',1,1);
INSERT INTO `column_entries` VALUES (3,'ktcore.columns.download','ktcore.views.browse','',2,0);
INSERT INTO `column_entries` VALUES (4,'ktcore.columns.creationdate','ktcore.views.browse','',3,0);
INSERT INTO `column_entries` VALUES (5,'ktcore.columns.modificationdate','ktcore.views.browse','',4,0);
INSERT INTO `column_entries` VALUES (6,'ktcore.columns.creator','ktcore.views.browse','',5,0);
INSERT INTO `column_entries` VALUES (7,'ktcore.columns.workflow_state','ktcore.views.browse','',6,0);

-- search
INSERT INTO `column_entries` VALUES (8,'ktcore.columns.selection','ktcore.views.search','',0,1);
INSERT INTO `column_entries` VALUES (9,'ktcore.columns.title','ktcore.views.search','',1,1);
INSERT INTO `column_entries` VALUES (10,'ktcore.columns.download','ktcore.views.search','',2,0);
INSERT INTO `column_entries` VALUES (11,'ktcore.columns.creationdate','ktcore.views.search','',3,0);
INSERT INTO `column_entries` VALUES (12,'ktcore.columns.modificationdate','ktcore.views.search','',4,0);
INSERT INTO `column_entries` VALUES (13,'ktcore.columns.creator','ktcore.views.search','',5,0);
INSERT INTO `column_entries` VALUES (14,'ktcore.columns.workflow_state','ktcore.views.search','',6,0);

CREATE TABLE `zseq_column_entries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 ;

INSERT INTO `zseq_column_entries` VALUES (14);