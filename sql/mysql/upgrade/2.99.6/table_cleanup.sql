-- Drop unused tables
DROP TABLE IF EXISTS `browse_criteria`;
DROP TABLE IF EXISTS `dependant_document_instance`;
DROP TABLE IF EXISTS `dependant_document_template`;
DROP TABLE IF EXISTS `groups_folders_approval_link`;
DROP TABLE IF EXISTS `groups_folders_link`;
DROP TABLE IF EXISTS `metadata_lookup_condition`;
DROP TABLE IF EXISTS `metadata_lookup_condition_chain`;
DROP TABLE IF EXISTS `zseq_metadata_lookup_condition`;
DROP TABLE IF EXISTS `zseq_metadata_lookup_condition_chain`;
DROP TABLE IF EXISTS `zseq_search_document_user_link`;
DROP TABLE IF EXISTS `zseq_web_documents`;
DROP TABLE IF EXISTS `zseq_web_documents_status_lookup`;
DROP TABLE IF EXISTS `zseq_web_sites`;

-- Make sure sequence tables are MyISAM to avoid transaction-safety.
ALTER TABLE `zseq_active_sessions` ENGINE=MyISAM;
ALTER TABLE `zseq_archive_restoration_request` ENGINE=MyISAM;
ALTER TABLE `zseq_archiving_settings` ENGINE=MyISAM;
ALTER TABLE `zseq_archiving_type_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_browse_criteria` ENGINE=MyISAM;
ALTER TABLE `zseq_data_types` ENGINE=MyISAM;
ALTER TABLE `zseq_dependant_document_instance` ENGINE=MyISAM;
ALTER TABLE `zseq_dependant_document_template` ENGINE=MyISAM;
ALTER TABLE `zseq_discussion_comments` ENGINE=MyISAM;
ALTER TABLE `zseq_discussion_threads` ENGINE=MyISAM;
ALTER TABLE `zseq_document_archiving_link` ENGINE=MyISAM;
ALTER TABLE `zseq_document_fields` ENGINE=MyISAM;
ALTER TABLE `zseq_document_fields_link` ENGINE=MyISAM;
ALTER TABLE `zseq_document_link` ENGINE=MyISAM;
ALTER TABLE `zseq_document_link_types` ENGINE=MyISAM;
ALTER TABLE `zseq_document_subscriptions` ENGINE=MyISAM;
ALTER TABLE `zseq_document_transaction_types_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_document_transactions` ENGINE=MyISAM;
ALTER TABLE `zseq_document_type_fields_link` ENGINE=MyISAM;
ALTER TABLE `zseq_document_types_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_documents` ENGINE=MyISAM;
ALTER TABLE `zseq_folder_doctypes_link` ENGINE=MyISAM;
ALTER TABLE `zseq_folder_subscriptions` ENGINE=MyISAM;
ALTER TABLE `zseq_folders` ENGINE=MyISAM;
ALTER TABLE `zseq_folders_users_roles_link` ENGINE=MyISAM;
ALTER TABLE `zseq_groups_folders_approval_link` ENGINE=MyISAM;
ALTER TABLE `zseq_groups_folders_link` ENGINE=MyISAM;
ALTER TABLE `zseq_groups_groups_link` ENGINE=MyISAM;
ALTER TABLE `zseq_groups_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_groups_units_link` ENGINE=MyISAM;
ALTER TABLE `zseq_help` ENGINE=MyISAM;
ALTER TABLE `zseq_help_replacement` ENGINE=MyISAM;
ALTER TABLE `zseq_links` ENGINE=MyISAM;
ALTER TABLE `zseq_metadata_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_mime_types` ENGINE=MyISAM;
ALTER TABLE `zseq_news` ENGINE=MyISAM;
ALTER TABLE `zseq_organisations_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_permission_assignments` ENGINE=MyISAM;
ALTER TABLE `zseq_permission_descriptors` ENGINE=MyISAM;
ALTER TABLE `zseq_permission_lookup_assignments` ENGINE=MyISAM;
ALTER TABLE `zseq_permission_lookups` ENGINE=MyISAM;
ALTER TABLE `zseq_permission_objects` ENGINE=MyISAM;
ALTER TABLE `zseq_permissions` ENGINE=MyISAM;
ALTER TABLE `zseq_roles` ENGINE=MyISAM;
ALTER TABLE `zseq_status_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_system_settings` ENGINE=MyISAM;
ALTER TABLE `zseq_time_period` ENGINE=MyISAM;
ALTER TABLE `zseq_time_unit_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_units_lookup` ENGINE=MyISAM;
ALTER TABLE `zseq_units_organisations_link` ENGINE=MyISAM;
ALTER TABLE `zseq_upgrades` ENGINE=MyISAM;
ALTER TABLE `zseq_users` ENGINE=MyISAM;
ALTER TABLE `zseq_users_groups_link` ENGINE=MyISAM;

ALTER TABLE `active_sessions` TYPE=InnoDB;
ALTER TABLE `archive_restoration_request` TYPE=InnoDB;
ALTER TABLE `archiving_settings` TYPE=InnoDB;
ALTER TABLE `archiving_type_lookup` TYPE=InnoDB;
ALTER TABLE `data_types` TYPE=InnoDB;
ALTER TABLE `discussion_comments` TYPE=InnoDB;
ALTER TABLE `discussion_threads` TYPE=InnoDB;
ALTER TABLE `document_archiving_link` TYPE=InnoDB;
ALTER TABLE `document_fields` TYPE=InnoDB;
ALTER TABLE `document_fields_link` TYPE=InnoDB;
ALTER TABLE `document_link` TYPE=InnoDB;
ALTER TABLE `document_subscriptions` TYPE=InnoDB;
ALTER TABLE `document_transaction_types_lookup` TYPE=InnoDB;
ALTER TABLE `document_transactions` TYPE=InnoDB;
ALTER TABLE `document_type_fields_link` TYPE=InnoDB;
ALTER TABLE `document_types_lookup` TYPE=InnoDB;
ALTER TABLE `documents` TYPE=InnoDB;
ALTER TABLE `folder_doctypes_link` TYPE=InnoDB;
ALTER TABLE `folder_subscriptions` TYPE=InnoDB;
ALTER TABLE `folders` TYPE=InnoDB;
ALTER TABLE `folders_users_roles_link` TYPE=InnoDB;
ALTER TABLE `groups_lookup` TYPE=InnoDB;
ALTER TABLE `groups_units_link` TYPE=InnoDB;
ALTER TABLE `help` TYPE=InnoDB;
ALTER TABLE `links` TYPE=InnoDB;
ALTER TABLE `metadata_lookup` TYPE=InnoDB;
ALTER TABLE `mime_types` TYPE=InnoDB;
ALTER TABLE `news` TYPE=InnoDB;
ALTER TABLE `organisations_lookup` TYPE=InnoDB;
ALTER TABLE `roles` TYPE=InnoDB;
ALTER TABLE `status_lookup` TYPE=InnoDB;
ALTER TABLE `system_settings` TYPE=InnoDB;
ALTER TABLE `time_period` TYPE=InnoDB;
ALTER TABLE `time_unit_lookup` TYPE=InnoDB;
ALTER TABLE `units_lookup` TYPE=InnoDB;
ALTER TABLE `units_organisations_link` TYPE=InnoDB;
ALTER TABLE `users` TYPE=InnoDB;
ALTER TABLE `users_groups_link` TYPE=InnoDB;
ALTER TABLE `web_documents` TYPE=InnoDB;
ALTER TABLE `web_documents_status_lookup` TYPE=InnoDB;
ALTER TABLE `web_sites` TYPE=InnoDB;

