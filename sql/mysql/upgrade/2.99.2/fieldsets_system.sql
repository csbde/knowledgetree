ALTER TABLE `fieldsets` ADD COLUMN `is_system` tinyint(1) unsigned NOT NULL default '0';
ALTER TABLE `fieldsets` ADD INDEX `is_system` (`is_system`);
