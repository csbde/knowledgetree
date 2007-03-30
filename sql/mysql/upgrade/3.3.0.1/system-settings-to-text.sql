TRUNCATE `zseq_upgrades`;
INSERT INTO `zseq_upgrades` SELECT MAX(`id`) FROM `upgrades`;
ALTER TABLE `system_settings` CHANGE value value TEXT;
