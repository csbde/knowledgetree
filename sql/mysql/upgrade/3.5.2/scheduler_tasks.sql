ALTER TABLE  `scheduler_tasks` ADD  `status` ENUM(  'enabled',  'disabled',  'system' ) NOT NULL DEFAULT  'disabled';

UPDATE  `scheduler_tasks` SET  `status` =  'system' WHERE  `task` = 'Indexing' OR `task` = 'Index Migration' OR `task` = 'Index Optimisation';

select @id:=ifnull(max(id),0)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Periodic Document Expunge','bin/expungeall.php','',0,'weekly','2007-10-01',NULL,0,'disabled');

select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Database Maintenance','bin/dbmaint.php','optimize',0,'monthly','2007-10-01',NULL,0,'disabled');

select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Open Office Test','bin/checkopenoffice.php','',0,'1min','2007-10-01',NULL,0,'enabled');

UPDATE zseq_scheduler_tasks set id=@id;