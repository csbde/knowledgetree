SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `active_sessions` CHANGE COLUMN `session_id` `session_id` char(255) default NULL; # was varchar(255) default NULL
ALTER TABLE `active_sessions` CHANGE COLUMN `ip` `ip` char(30) default NULL; # was varchar(30) default NULL
ALTER TABLE `archiving_type_lookup` CHANGE COLUMN `name` `name` char(100) default NULL; # was varchar(100) default NULL
ALTER TABLE `data_types` CHANGE COLUMN `name` `name` char(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `document_fields` CHANGE COLUMN `data_type` `data_type` varchar(100) NOT NULL default ''; # was varchar(100) default NULL
ALTER TABLE `document_fields` CHANGE COLUMN `name` `name` varchar(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `document_fields_link` CHANGE COLUMN `value` `value` char(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `document_transactions` CHANGE COLUMN `ip` `ip` char(30) default NULL; # was varchar(30) default NULL
ALTER TABLE `document_transactions` CHANGE COLUMN `transaction_namespace` `transaction_namespace` char(255) NOT NULL default 'ktcore.transactions.event'; # was varchar(255) NOT NULL default 'ktcore.transactions.event'
ALTER TABLE `document_transactions` CHANGE COLUMN `comment` `comment` char(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `document_transactions` CHANGE COLUMN `filename` `filename` char(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `document_transactions` CHANGE COLUMN `version` `version` char(50) default NULL; # was varchar(50) default NULL
ALTER TABLE `document_types_lookup` CHANGE COLUMN `name` `name` char(100) default NULL; # was varchar(100) default NULL
ALTER TABLE `groups_lookup` CHANGE COLUMN `name` `name` char(100) NOT NULL default ''; # was varchar(100) default NULL
DROP TABLE IF EXISTS `language_lookup`;
ALTER TABLE `links` CHANGE COLUMN `url` `url` char(100) NOT NULL default ''; # was varchar(100) default NULL
ALTER TABLE `links` CHANGE COLUMN `name` `name` char(100) NOT NULL default ''; # was varchar(100) default NULL
ALTER TABLE `metadata_lookup` CHANGE COLUMN `name` `name` char(255) default NULL; # was varchar(255) default NULL
ALTER TABLE `mime_types` CHANGE COLUMN `filetypes` `filetypes` char(100) NOT NULL default ''; # was varchar(100) default NULL
ALTER TABLE `mime_types` CHANGE COLUMN `mimetypes` `mimetypes` char(100) NOT NULL default ''; # was varchar(100) default NULL
ALTER TABLE `mime_types` CHANGE COLUMN `icon_path` `icon_path` char(255) default NULL; # was varchar(255) default NULL
ALTER TABLE `notifications` CHANGE COLUMN `data_str_1` `data_str_1` varchar(255) default NULL; # was varchar(255) NOT NULL default ''
ALTER TABLE `notifications` CHANGE COLUMN `data_int_2` `data_int_2` int(11) default NULL; # was int(11) NOT NULL default '0'
ALTER TABLE `notifications` CHANGE COLUMN `data_int_1` `data_int_1` int(11) default NULL; # was int(11) NOT NULL default '0'
ALTER TABLE `notifications` CHANGE COLUMN `data_str_2` `data_str_2` varchar(255) default NULL; # was varchar(255) NOT NULL default ''
ALTER TABLE `organisations_lookup` CHANGE COLUMN `name` `name` char(100) NOT NULL default ''; # was varchar(100) default NULL
ALTER TABLE `roles` CHANGE COLUMN `name` `name` char(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `status_lookup` CHANGE COLUMN `name` `name` char(255) default NULL; # was varchar(255) default NULL
ALTER TABLE `system_settings` CHANGE COLUMN `value` `value` char(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `system_settings` CHANGE COLUMN `name` `name` char(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `time_unit_lookup` CHANGE COLUMN `name` `name` char(100) default NULL; # was varchar(100) default NULL
ALTER TABLE `units_lookup` CHANGE COLUMN `name` `name` char(100) NOT NULL default ''; # was varchar(100) default NULL
ALTER TABLE `users` CHANGE COLUMN `password` `password` varchar(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `users` CHANGE COLUMN `username` `username` varchar(255) NOT NULL default ''; # was varchar(255) default NULL
ALTER TABLE `users` CHANGE COLUMN `name` `name` varchar(255) NOT NULL default ''; # was varchar(255) default NULL
DROP TABLE IF EXISTS `zseq_groups_folders_approval_link`;
DROP TABLE IF EXISTS `zseq_groups_folders_link`;
DROP TABLE IF EXISTS `web_documents`;
DROP TABLE IF EXISTS `web_documents_status_lookup`;
DROP TABLE IF EXISTS `web_sites`;
SET FOREIGN_KEY_CHECKS=1;
