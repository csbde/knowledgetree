select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Refresh Index Statistics','search2/bin/cronIndexStats.php','',0,'1min','2007-10-01',NULL,0,'enabled');

select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Refresh Resource Dependancies','search2/bin/cronResources.php','',0,'1min','2007-10-01',NULL,0,'enabled');

UPDATE zseq_scheduler_tasks set id=@id;

UPDATE scheduler_tasks set task='Index Optimization', script_url = 'search2/bin/cronOptimize.php' where script_url = 'search2/bin/optimise.php';