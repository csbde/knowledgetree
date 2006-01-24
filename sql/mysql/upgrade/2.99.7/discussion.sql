ALTER TABLE `discussion_threads` ADD COLUMN `close_reason` text NOT NULL;
ALTER TABLE `discussion_threads` ADD COLUMN `close_metadata_version` int(11) NOT NULL default '0';
ALTER TABLE `discussion_threads` ADD COLUMN `state` int(1) NOT NULL default '0';

