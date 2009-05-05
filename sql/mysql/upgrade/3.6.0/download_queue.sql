--
-- Table structure for table `download_queue`
--

CREATE TABLE `download_queue` (
	`code` char(16) NOT NULL,
	`folder_id` int(11) NOT NULL,
	`object_id` int(11) NOT NULL,
	`object_type` enum('document', 'folder') NOT NULL default 'folder',
	`user_id` int(11) NOT NULL,
	`date_added` timestamp NOT NULL default CURRENT_TIMESTAMP,
	`status` tinyint(4) NOT NULL default 0,
	`errors` mediumtext,
	INDEX (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('export', 'Use External Zip Binary', 'Utilises the external zip binary for compressing archives. The default is to use the PEAR archive class.', 'useBinary', 'default', 'false', 'boolean', NULL, 1),
('export', 'Use Bulk Download Queue', 'The bulk download can be large and can prevent normal browsing. The download queue performs the bulk downloads in
the background.', 'useDownloadQueue', 'default', 'true', 'boolean', NULL, 1);

INSERT INTO `scheduler_tasks` (task, script_url, frequency, run_time, status)
VALUES ('Bulk Download Queue','bin/ajaxtasks/downloadTask.php','30secs','2007-10-01','system');