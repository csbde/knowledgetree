select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Disk Usage and Folder Utilisation Statistics','plugins/housekeeper/bin/UpdateStats.php','',0,'5mins','2007-10-01',NULL,0,'enabled');

select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Check Latest Version','plugins/ktstandard/AdminVersionPlugin/bin/UpdateNewVersion.php','',0,'daily','2007-10-01',NULL,0,'enabled');

UPDATE zseq_scheduler_tasks set id=@id;