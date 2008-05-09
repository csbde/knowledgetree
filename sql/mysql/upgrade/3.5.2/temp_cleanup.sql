select @id:=ifnull(max(id),0)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Cleanup Temporary Directory','search2/bin/cronCleanup.php','',0,'1min','2007-10-01',NULL,0,'enabled');

UPDATE zseq_scheduler_tasks set id=@id;