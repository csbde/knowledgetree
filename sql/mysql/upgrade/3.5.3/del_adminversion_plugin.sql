-- Remove from plugins table
DELETE FROM plugins WHERE namespace = 'ktstandard.adminversion.plugin';

-- Remove from scheduler tasks table
DELETE FROM scheduler_tasks WHERE script_url = 'plugins/ktstandard/AdminVersionPlugin/bin/UpdateNewVersion.php';

