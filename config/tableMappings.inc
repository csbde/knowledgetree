<?php
/**
 * $Id$
 *
 * Stores the table mappings.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 */
 
// Table mappings
// session information
$default->owl_sessions_table = "active_sessions";
//data types table
$default->owl_data_types_table ="data_types";
// document type fields
$default->owl_fields_table = "document_fields";
// links document
$default->owl_document_fields_table = "document_fields_link";
// meta data value lookup table
$default->owl_document_fields_lookup_tables = "metadata_lookup";
// document subscriptions
$default->owl_document_subscriptions_table = "document_subscriptions";
// document transaction types
$default->owl_transaction_types_table = "document_transaction_types_lookup";
// document transactions
$default->owl_document_transactions_table = "document_transactions";
// links document types to document type fields
$default->owl_document_type_fields_table = "document_type_fields_link";
// document type information
$default->owl_document_types_table = "document_types_lookup";
// links documents to words
$default->owl_document_words_table = "document_words_link";
// stores documents
$default->owl_documents_table = "documents";
//link folders to doc types
$default->owl_folder_doctypes_table = "folder_doctypes_link";
// stores folder subscriptions
$default->owl_folder_subscriptions_table = "folder_subscriptions";
// stores folders
$default->owl_folders_table = "folders";
// links folders to users (and roles) for approval collaboration
$default->owl_folders_user_roles_table = "folders_users_roles_link";
// stores approval collaboration information- approval roles mapped to folders with order
$default->owl_groups_folders_approval_table = "groups_folders_approval_link";
// links groups to folders
$default->owl_groups_folders_table = "groups_folders_link";
// stores group information
$default->owl_groups_table = "groups_lookup";
// links groups to units
$default->owl_groups_units_table = "groups_units_link";
// links
$default->owl_links_table = "links";
// Table with metadata
$default->owl_metadata_table = "metadata_lookup";
// Table with mime info
$default->owl_mime_table = "mime_types";
// dashboard news table
$default->owl_news_table = "news";
// organisation information
$default->owl_organisations_table = "organisations_lookup";
// stores role information (name and access)
$default->owl_roles_table = "roles";
// sitemap access classes
$default->owl_site_access_table = "site_access_lookup";
// sitemap sections
$default->owl_site_sections_table = "site_sections_lookup";
// sitemap definition
$default->owl_sitemap_table = "sitemap";
// stores document subscription information
$default->owl_subscriptions_table = "subscriptions";
// stores deleted files
$default->owl_sys_deleted_table = "sys_deleted";
// stores default system settings
$default->owl_system_settings_table = "system_settings";
// Table with discussion threads 
$default->owl_discussion_threads_table = "discussion_threads";
// Table with discussion comments
$default->owl_discussion_comments_table = "discussion_comments";
// Table with unit information
$default->owl_units_table = "units_lookup";
// Table with unit organisation link tables
$default->owl_units_organisations_table = "units_organisations_link";
// Table with user info
$default->owl_users_table = "users";
// links groups to users
$default->owl_users_groups_table = "users_groups_link";
// Table with web documents info for web publishing
$default->owl_web_documents_table = "web_documents";
// Table with web documents info for web publishing
$default->owl_web_documents_status_table = "web_documents_status_lookup";
// stores websites for web publishing
$default->owl_web_sites_table = "web_sites";
// stores indexed words
$default->owl_words_lookup_table = "words_lookup";
//stores help text
$default->owl_help_table = "help";
$default->owl_document_text_table = "document_text";
$default->owl_dependant_document_template_table = "dependant_document_template";
$default->owl_dependant_document_instance_table = "dependant_document_instance";
$default->owl_document_link_table = "document_link";
// archive settings
$default->owl_document_archiving_table = "document_archiving_link";
$default->owl_archiving_type_lookup_table = "archiving_type_lookup";
$default->owl_archiving_settings_table = "archiving_settings";
$default->owl_time_period_table = "time_period";
$default->owl_time_unit_lookup_table = "time_unit_lookup";
$default->owl_archive_restoration_table = "archive_restoration_request";
$default->owl_status_table = "status_lookup";
$default->search_permissions_table = "search_document_user_link";
?>