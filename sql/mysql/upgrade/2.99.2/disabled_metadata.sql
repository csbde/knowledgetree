ALTER TABLE `metadata_lookup` ADD COLUMN `disabled` tinyint(3) unsigned NOT NULL default '0';
ALTER TABLE `metadata_lookup` ADD INDEX `disabled` (`disabled`);
ALTER TABLE `metadata_lookup` ADD COLUMN `is_stuck` tinyint(1) NOT NULL default '0';
ALTER TABLE `metadata_lookup` ADD INDEX `is_stuck` (`is_stuck`);
