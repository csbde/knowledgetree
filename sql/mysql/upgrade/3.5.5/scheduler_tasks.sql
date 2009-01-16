select @id:=ifnull(max(id),0)+1 from scheduler_tasks;
INSERT INTO `scheduler_tasks` VALUES (@id,'Document Processor','search2/bin/cronDocumentProcessor.php','',0,'1min','2007-10-01',NULL,0,'system');

DELETE FROM scheduler_tasks WHERE task = 'Indexing';
DELETE FROM scheduler_tasks WHERE task = 'Refresh Index Statistics';
DELETE FROM scheduler_tasks WHERE task = 'Refresh Resource Dependancies';