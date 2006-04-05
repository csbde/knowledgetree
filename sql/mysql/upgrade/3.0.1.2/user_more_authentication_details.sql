ALTER TABLE `users` ADD COLUMN `authentication_details_b1` tinyint(1) default NULL;
ALTER TABLE `users` ADD COLUMN `authentication_details_i2` int(11) default NULL;
ALTER TABLE `users` ADD COLUMN `authentication_details_d1` datetime default NULL;
ALTER TABLE `users` ADD COLUMN `authentication_details_i1` int(11) default NULL;
ALTER TABLE `users` ADD COLUMN `authentication_details_d2` datetime default NULL;
ALTER TABLE `users` ADD COLUMN `authentication_details_b2` tinyint(1) default NULL;
ALTER TABLE `users` ADD INDEX `authentication_details_b1` (`authentication_details_b1`);
ALTER TABLE `users` ADD INDEX `authentication_details_b2` (`authentication_details_b2`);
