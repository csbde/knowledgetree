ALTER TABLE `folders` ADD `owner_id` int(11) NOT NULL default '0';
UPDATE `folders` SET `owner_id` = `creator_id`;
