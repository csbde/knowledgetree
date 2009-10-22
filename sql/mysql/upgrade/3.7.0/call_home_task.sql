INSERT INTO `scheduler_tasks` (task, script_url, is_complete, frequency, run_time, status)
VALUES ('Call Home','bin/system_info.php', 1, 'half_hourly','2009-10-01','system');