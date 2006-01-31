SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `groups_lookup` ADD COLUMN `unit_id` int(11) default NULL;
ALTER TABLE `groups_lookup` ADD INDEX `unit_id` (`unit_id`);
ALTER TABLE `units_lookup` ADD COLUMN `folder_id` int(11) NOT NULL;
ALTER TABLE `units_lookup` ADD UNIQUE `folder_id` (`folder_id`);
ALTER TABLE `groups_lookup` TYPE=InnoDB;
ALTER TABLE `groups_lookup` ADD CONSTRAINT `groups_lookup_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units_lookup` (`id`);
SET FOREIGN_KEY_CHECKS=1;
