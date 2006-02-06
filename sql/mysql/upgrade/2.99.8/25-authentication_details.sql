ALTER TABLE `users` CHANGE `ldap_dn` `authentication_details_s1` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE `users` CHANGE `authentication_details` `authentication_details_s2` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE `groups_lookup` ADD COLUMN `authentication_details_s2` varchar(255) default NULL;
ALTER TABLE `groups_lookup` ADD COLUMN `authentication_details_s1` varchar(255) default NULL;
ALTER TABLE `groups_lookup` ADD INDEX `authentication_details_s1` (`authentication_details_s1`);
ALTER TABLE `groups_lookup` ADD `authentication_source_id` INT NULL ;
ALTER TABLE `groups_lookup` ADD INDEX ( `authentication_source_id` ) ;
