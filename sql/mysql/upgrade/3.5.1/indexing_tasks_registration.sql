DELETE FROM scheduler_tasks where task in ('Indexing','Index Migration','Index Optimisation');

select @id:=ifnull(max(id),0)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` (id,task,script_url,script_params,is_complete,frequency,run_time,previous_run_time,run_duration) VALUES (@id,'Indexing','search2/indexing/bin/cronIndexer.php','',0,'1min','2007-10-01',NULL,0);

select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` (id,task,script_url,script_params,is_complete,frequency,run_time,previous_run_time,run_duration) VALUES (@id,'Index Migration','search2/indexing/bin/cronMigration.php','',0,'5mins','2007-10-01',NULL,0);

select @id:=max(id)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` (id,task,script_url,script_params,is_complete,frequency,run_time,previous_run_time,run_duration) VALUES (@id,'Index Optimisation','search2/indexing/bin/optimise.php','',0,'weekly','2007-10-01',NULL,0);

UPDATE zseq_scheduler_tasks set id=@id;