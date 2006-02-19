ALTER TABLE `folders` DROP INDEX `fk_unit_id`; # was INDEX (`unit_id`)
ALTER TABLE `folders` DROP COLUMN `unit_id`; # was int(11) default NULL
ALTER TABLE `units_lookup` CHANGE COLUMN `folder_id` `folder_id` int(11) NOT NULL default '0';
DROP TABLE `groups_units_link`;
DROP TABLE `zseq_groups_units_link`;
