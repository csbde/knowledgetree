ALTER TABLE `folders` DROP INDEX `fk_unit_id`; # was INDEX (`unit_id`)
ALTER TABLE `folders` DROP COLUMN `unit_id`; # was int(11) default NULL
DROP TABLE `groups_units_link`;
DROP TABLE `zseq_groups_units_link`;
