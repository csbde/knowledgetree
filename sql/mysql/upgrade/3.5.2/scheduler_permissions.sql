UPDATE scheduler_tasks SET script_url='search2/bin/cronIndexer.php' WHERE task='Indexing';
UPDATE scheduler_tasks SET script_url='search2/bin/cronMigration.php' WHERE task='Index Migration';
UPDATE scheduler_tasks SET script_url='search2/bin/optimise.php' WHERE task='Index Optimisation';