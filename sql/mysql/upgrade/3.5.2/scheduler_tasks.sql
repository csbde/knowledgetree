ALTER TABLE  `scheduler_tasks` ADD  `status` ENUM(  'enabled',  'disabled',  'system' ) NOT NULL DEFAULT  'disabled';

UPDATE  `scheduler_tasks` SET  `status` =  'system' WHERE  `task` = 'Indexing' OR `task` = 'Index Migration' OR `task` = 'Index Optimisation';

INSERT INTO `scheduler_tasks` VALUES (4,'Periodic Document Expunge','bin/expungeall.php','',0,'weekly','2007-10-01',NULL,0,'disabled');