UPDATE system_settings SET value="1.2.5" WHERE name="knowledgeTreeVersion";

DROP TABLE IF EXISTS zseq_active_sessions;
CREATE TABLE zseq_active_sessions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_active_sessions` SELECT MAX(`id`) FROM `active_sessions`;
ALTER TABLE `active_sessions` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_archive_restoration_request;
CREATE TABLE zseq_archive_restoration_request (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_archive_restoration_request` SELECT MAX(`id`) FROM `archive_restoration_request`;
ALTER TABLE `archive_restoration_request` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_archiving_settings;
CREATE TABLE zseq_archiving_settings (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_archiving_settings` SELECT MAX(`id`) FROM `archiving_settings`;
ALTER TABLE `archiving_settings` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_archiving_type_lookup;
CREATE TABLE zseq_archiving_type_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_archiving_type_lookup` SELECT MAX(`id`) FROM `archiving_type_lookup`;
ALTER TABLE `archiving_type_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_data_types;
CREATE TABLE zseq_data_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_data_types` SELECT MAX(`id`) FROM `data_types`;
ALTER TABLE `data_types` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_dependant_document_instance;
CREATE TABLE zseq_dependant_document_instance (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_dependant_document_instance` SELECT MAX(`id`) FROM `dependant_document_instance`;
ALTER TABLE `dependant_document_instance` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_dependant_document_template;
CREATE TABLE zseq_dependant_document_template (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_dependant_document_template` SELECT MAX(`id`) FROM `dependant_document_template`;
ALTER TABLE `dependant_document_template` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_discussion_comments;
CREATE TABLE zseq_discussion_comments (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_discussion_comments` SELECT MAX(`id`) FROM `discussion_comments`;
ALTER TABLE `discussion_comments` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_discussion_threads;
CREATE TABLE zseq_discussion_threads (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_discussion_threads` SELECT MAX(`id`) FROM `discussion_threads`;
ALTER TABLE `discussion_threads` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_archiving_link;
CREATE TABLE zseq_document_archiving_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_archiving_link` SELECT MAX(`id`) FROM `document_archiving_link`;
ALTER TABLE `document_archiving_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_fields;
CREATE TABLE zseq_document_fields (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_fields` SELECT MAX(`id`) FROM `document_fields`;
ALTER TABLE `document_fields` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_fields_link;
CREATE TABLE zseq_document_fields_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_fields_link` SELECT MAX(`id`) FROM `document_fields_link`;
ALTER TABLE `document_fields_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_link;
CREATE TABLE zseq_document_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_link` SELECT MAX(`id`) FROM `document_link`;
ALTER TABLE `document_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_subscriptions;
CREATE TABLE zseq_document_subscriptions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_subscriptions` SELECT MAX(`id`) FROM `document_subscriptions`;
ALTER TABLE `document_subscriptions` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_text;
CREATE TABLE zseq_document_text (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_text` SELECT MAX(`id`) FROM `document_text`;
ALTER TABLE `document_text` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_transaction_types_lookup;
CREATE TABLE zseq_document_transaction_types_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_transaction_types_lookup` SELECT MAX(`id`) FROM `document_transaction_types_lookup`;
ALTER TABLE `document_transaction_types_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_transactions;
CREATE TABLE zseq_document_transactions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_transactions` SELECT MAX(`id`) FROM `document_transactions`;
ALTER TABLE `document_transactions` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_type_fields_link;
CREATE TABLE zseq_document_type_fields_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_type_fields_link` SELECT MAX(`id`) FROM `document_type_fields_link`;
ALTER TABLE `document_type_fields_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_document_types_lookup;
CREATE TABLE zseq_document_types_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_document_types_lookup` SELECT MAX(`id`) FROM `document_types_lookup`;
ALTER TABLE `document_types_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_documents;
CREATE TABLE zseq_documents (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_documents` SELECT MAX(`id`) FROM `documents`;
ALTER TABLE `documents` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_folder_doctypes_link;
CREATE TABLE zseq_folder_doctypes_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_folder_doctypes_link` SELECT MAX(`id`) FROM `folder_doctypes_link`;
ALTER TABLE `folder_doctypes_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_folder_subscriptions;
CREATE TABLE zseq_folder_subscriptions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_folder_subscriptions` SELECT MAX(`id`) FROM `folder_subscriptions`;
ALTER TABLE `folder_subscriptions` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_folders;
CREATE TABLE zseq_folders (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_folders` SELECT MAX(`id`) FROM `folders`;
ALTER TABLE `folders` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_folders_users_roles_link;
CREATE TABLE zseq_folders_users_roles_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_folders_users_roles_link` SELECT MAX(`id`) FROM `folders_users_roles_link`;
ALTER TABLE `folders_users_roles_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_groups_folders_approval_link;
CREATE TABLE zseq_groups_folders_approval_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_groups_folders_approval_link` SELECT MAX(`id`) FROM `groups_folders_approval_link`;
ALTER TABLE `groups_folders_approval_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_groups_folders_link;
CREATE TABLE zseq_groups_folders_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_groups_folders_link` SELECT MAX(`id`) FROM `groups_folders_link`;
ALTER TABLE `groups_folders_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_groups_lookup;
CREATE TABLE zseq_groups_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_groups_lookup` SELECT MAX(`id`) FROM `groups_lookup`;
ALTER TABLE `groups_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_groups_units_link;
CREATE TABLE zseq_groups_units_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_groups_units_link` SELECT MAX(`id`) FROM `groups_units_link`;
ALTER TABLE `groups_units_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_help;
CREATE TABLE zseq_help (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_help` SELECT MAX(`id`) FROM `help`;
ALTER TABLE `help` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_links;
CREATE TABLE zseq_links (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_links` SELECT MAX(`id`) FROM `links`;
ALTER TABLE `links` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_metadata_lookup;
CREATE TABLE zseq_metadata_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_metadata_lookup` SELECT MAX(`id`) FROM `metadata_lookup`;
ALTER TABLE `metadata_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_mime_types;
CREATE TABLE zseq_mime_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;
ALTER TABLE `mime_types` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_news;
CREATE TABLE zseq_news (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_news` SELECT MAX(`id`) FROM `news`;
ALTER TABLE `news` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_organisations_lookup;
CREATE TABLE zseq_organisations_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_organisations_lookup` SELECT MAX(`id`) FROM `organisations_lookup`;
ALTER TABLE `organisations_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_roles;
CREATE TABLE zseq_roles (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_roles` SELECT MAX(`id`) FROM `roles`;
ALTER TABLE `roles` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_search_document_user_link;
CREATE TABLE zseq_search_document_user_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_search_document_user_link` SELECT MAX(`id`) FROM `search_document_user_link`;
ALTER TABLE `search_document_user_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_status_lookup;
CREATE TABLE zseq_status_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_status_lookup` SELECT MAX(`id`) FROM `status_lookup`;
ALTER TABLE `status_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_system_settings;
CREATE TABLE zseq_system_settings (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_system_settings` SELECT MAX(`id`) FROM `system_settings`;
ALTER TABLE `system_settings` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_time_period;
CREATE TABLE zseq_time_period (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_time_period` SELECT MAX(`id`) FROM `time_period`;
ALTER TABLE `time_period` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_time_unit_lookup;
CREATE TABLE zseq_time_unit_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_time_unit_lookup` SELECT MAX(`id`) FROM `time_unit_lookup`;
ALTER TABLE `time_unit_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_units_lookup;
CREATE TABLE zseq_units_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_units_lookup` SELECT MAX(`id`) FROM `units_lookup`;
ALTER TABLE `units_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_units_organisations_link;
CREATE TABLE zseq_units_organisations_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_units_organisations_link` SELECT MAX(`id`) FROM `units_organisations_link`;
ALTER TABLE `units_organisations_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_users;
CREATE TABLE zseq_users (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_users` SELECT MAX(`id`) FROM `users`;
ALTER TABLE `users` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_users_groups_link;
CREATE TABLE zseq_users_groups_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_users_groups_link` SELECT MAX(`id`) FROM `users_groups_link`;
ALTER TABLE `users_groups_link` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_web_documents;
CREATE TABLE zseq_web_documents (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_web_documents` SELECT MAX(`id`) FROM `web_documents`;
ALTER TABLE `web_documents` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_web_documents_status_lookup;
CREATE TABLE zseq_web_documents_status_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_web_documents_status_lookup` SELECT MAX(`id`) FROM `web_documents_status_lookup`;
ALTER TABLE `web_documents_status_lookup` CHANGE `id` `id` INT( 11 ) NOT NULL;

DROP TABLE IF EXISTS zseq_web_sites;
CREATE TABLE zseq_web_sites (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO `zseq_web_sites` SELECT MAX(`id`) FROM `web_sites`;
ALTER TABLE `web_sites` CHANGE `id` `id` INT( 11 ) NOT NULL;


ALTER TABLE `users` ADD UNIQUE (
 `username` 
); 
ALTER TABLE `document_types_lookup` ADD UNIQUE (
 `name` 
);
ALTER TABLE `groups_lookup` ADD UNIQUE (
 `name` 
); 
ALTER TABLE `organisations_lookup` ADD UNIQUE (
 `name` 
); 
ALTER TABLE `roles` ADD UNIQUE (
 `name` 
); 
ALTER TABLE `units_lookup` ADD UNIQUE (
`name`
)

