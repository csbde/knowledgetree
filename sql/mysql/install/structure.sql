-- phpMyAdmin SQL Dump
-- version 2.6.1-rc1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jun 13, 2005 at 10:13 PM
-- Server version: 4.0.23
-- PHP Version: 4.3.10-10ubuntu4
-- 
-- Database: `pristine`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `active_sessions`
-- 

CREATE TABLE active_sessions (
  id int(11) NOT NULL default '0',
  user_id int(11) default NULL,
  session_id char(255) default NULL,
  lastused datetime default NULL,
  ip char(30) default NULL,
  UNIQUE KEY id (id),
  KEY session_id_idx (session_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `archive_restoration_request`
-- 

CREATE TABLE archive_restoration_request (
  id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  request_user_id int(11) NOT NULL default '0',
  admin_user_id int(11) NOT NULL default '0',
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `archiving_settings`
-- 

CREATE TABLE archiving_settings (
  id int(11) NOT NULL default '0',
  archiving_type_id int(11) NOT NULL default '0',
  expiration_date date default NULL,
  document_transaction_id int(11) default NULL,
  time_period_id int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `archiving_type_lookup`
-- 

CREATE TABLE archiving_type_lookup (
  id int(11) NOT NULL default '0',
  name char(100) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `browse_criteria`
-- 

CREATE TABLE browse_criteria (
  id int(11) NOT NULL default '0',
  criteria_id int(11) NOT NULL default '0',
  precedence int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY criteria_id (criteria_id),
  UNIQUE KEY precedence (precedence)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `data_types`
-- 

CREATE TABLE data_types (
  id int(11) NOT NULL default '0',
  name char(255) NOT NULL default '',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `dependant_document_instance`
-- 

CREATE TABLE dependant_document_instance (
  id int(11) NOT NULL default '0',
  document_title text NOT NULL,
  user_id int(11) NOT NULL default '0',
  template_document_id int(11) default NULL,
  parent_document_id int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `dependant_document_template`
-- 

CREATE TABLE dependant_document_template (
  id int(11) NOT NULL default '0',
  document_title text NOT NULL,
  default_user_id int(11) NOT NULL default '0',
  template_document_id int(11) default NULL,
  group_folder_approval_link_id int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `discussion_comments`
-- 

CREATE TABLE discussion_comments (
  id int(11) NOT NULL default '0',
  thread_id int(11) NOT NULL default '0',
  in_reply_to int(11) default NULL,
  user_id int(11) NOT NULL default '0',
  subject text,
  body text,
  date datetime default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `discussion_threads`
-- 

CREATE TABLE discussion_threads (
  id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  first_comment_id int(11) NOT NULL default '0',
  last_comment_id int(11) NOT NULL default '0',
  views int(11) NOT NULL default '0',
  replies int(11) NOT NULL default '0',
  creator_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_archiving_link`
-- 

CREATE TABLE document_archiving_link (
  id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  archiving_settings_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_fields`
-- 

CREATE TABLE document_fields (
  id int(11) NOT NULL default '0',
  name char(255) NOT NULL default '',
  data_type char(100) NOT NULL default '',
  is_generic tinyint(1) default NULL,
  has_lookup tinyint(1) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_fields_link`
-- 

CREATE TABLE document_fields_link (
  id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  document_field_id int(11) NOT NULL default '0',
  value char(255) NOT NULL default '',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_link`
-- 

CREATE TABLE document_link (
  id int(11) NOT NULL default '0',
  parent_document_id int(11) NOT NULL default '0',
  child_document_id int(11) NOT NULL default '0',
  link_type_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_link_types`
-- 

CREATE TABLE document_link_types (
  id int(11) NOT NULL default '0',
  name char(100) NOT NULL default '',
  reverse_name char(100) NOT NULL default '',
  description char(255) NOT NULL default '',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_subscriptions`
-- 

CREATE TABLE document_subscriptions (
  id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  is_alerted tinyint(1) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_text`
-- 

CREATE TABLE document_text (
  document_id int(11) default NULL,
  document_text mediumtext,
  KEY document_text_document_id_indx (document_id),
  FULLTEXT KEY document_text (document_text)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_transaction_types_lookup`
-- 

CREATE TABLE document_transaction_types_lookup (
  id int(11) NOT NULL default '0',
  name char(100) NOT NULL default '',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_transactions`
-- 

CREATE TABLE document_transactions (
  id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  version char(50) default NULL,
  user_id int(11) NOT NULL default '0',
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  ip char(30) default NULL,
  filename char(255) NOT NULL default '',
  comment char(255) NOT NULL default '',
  transaction_id int(11) default NULL,
  UNIQUE KEY id (id),
  KEY fk_document_id (document_id),
  KEY fk_user_id (user_id),
  KEY fk_transaction_id (transaction_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_type_fields_link`
-- 

CREATE TABLE document_type_fields_link (
  id int(11) NOT NULL default '0',
  document_type_id int(11) NOT NULL default '0',
  field_id int(11) NOT NULL default '0',
  is_mandatory tinyint(1) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `document_types_lookup`
-- 

CREATE TABLE document_types_lookup (
  id int(11) NOT NULL default '0',
  name char(100) default NULL,
  UNIQUE KEY id (id),
  UNIQUE KEY name (name)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `documents`
-- 

CREATE TABLE documents (
  id int(11) NOT NULL default '0',
  document_type_id int(11) NOT NULL default '0',
  name text NOT NULL,
  filename text NOT NULL,
  size bigint(20) NOT NULL default '0',
  creator_id int(11) NOT NULL default '0',
  modified datetime NOT NULL default '0000-00-00 00:00:00',
  description varchar(200) NOT NULL default '',
  security int(11) NOT NULL default '0',
  mime_id int(11) NOT NULL default '0',
  folder_id int(11) NOT NULL default '0',
  major_version int(11) NOT NULL default '0',
  minor_version int(11) NOT NULL default '0',
  is_checked_out tinyint(1) NOT NULL default '0',
  parent_folder_ids text,
  full_path text,
  checked_out_user_id int(11) default NULL,
  status_id int(11) default NULL,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY id (id),
  KEY fk_document_type_id (document_type_id),
  KEY fk_creator_id (creator_id),
  KEY fk_folder_id (folder_id),
  KEY fk_checked_out_user_id (checked_out_user_id),
  KEY fk_status_id (status_id),
  KEY created (created)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `folder_doctypes_link`
-- 

CREATE TABLE folder_doctypes_link (
  id int(11) NOT NULL default '0',
  folder_id int(11) NOT NULL default '0',
  document_type_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id),
  KEY fk_folder_id (folder_id),
  KEY fk_document_type_id (document_type_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `folder_subscriptions`
-- 

CREATE TABLE folder_subscriptions (
  id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  folder_id int(11) NOT NULL default '0',
  is_alerted tinyint(1) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `folders`
-- 

CREATE TABLE folders (
  id int(11) NOT NULL default '0',
  name varchar(255) default NULL,
  description varchar(255) default NULL,
  parent_id int(11) default NULL,
  creator_id int(11) default NULL,
  unit_id int(11) default NULL,
  is_public tinyint(1) NOT NULL default '0',
  parent_folder_ids text,
  full_path text,
  inherit_parent_folder_permission int(11) default NULL,
  permission_folder_id int(11) default NULL,
  UNIQUE KEY id (id),
  KEY fk_parent_id (parent_id),
  KEY fk_creator_id (creator_id),
  KEY fk_unit_id (unit_id),
  KEY permission_folder_id (permission_folder_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `folders_users_roles_link`
-- 

CREATE TABLE folders_users_roles_link (
  id int(11) NOT NULL default '0',
  group_folder_approval_id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  datetime datetime default NULL,
  done tinyint(1) default NULL,
  active tinyint(1) default NULL,
  dependant_documents_created tinyint(1) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_folders_approval_link`
-- 

CREATE TABLE groups_folders_approval_link (
  id int(11) NOT NULL default '0',
  folder_id int(11) NOT NULL default '0',
  group_id int(11) NOT NULL default '0',
  precedence int(11) NOT NULL default '0',
  role_id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_folders_link`
-- 

CREATE TABLE groups_folders_link (
  id int(11) NOT NULL default '0',
  group_id int(11) NOT NULL default '0',
  folder_id int(11) NOT NULL default '0',
  can_read tinyint(1) NOT NULL default '0',
  can_write tinyint(1) NOT NULL default '0',
  UNIQUE KEY id (id),
  KEY fk_group_id (group_id),
  KEY fk_folder_id (folder_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_lookup`
-- 

CREATE TABLE groups_lookup (
  id int(11) NOT NULL default '0',
  name char(100) NOT NULL default '',
  is_sys_admin tinyint(1) NOT NULL default '0',
  is_unit_admin tinyint(1) NOT NULL default '0',
  UNIQUE KEY id (id),
  UNIQUE KEY name (name)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_units_link`
-- 

CREATE TABLE groups_units_link (
  id int(11) NOT NULL default '0',
  group_id int(11) NOT NULL default '0',
  unit_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id),
  KEY fk_group_id (group_id),
  KEY fk_unit_id (unit_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `help`
-- 

CREATE TABLE help (
  id int(11) NOT NULL default '0',
  fSection varchar(100) NOT NULL default '',
  help_info text NOT NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `links`
-- 

CREATE TABLE links (
  id int(11) NOT NULL default '0',
  name char(100) NOT NULL default '',
  url char(100) NOT NULL default '',
  rank int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `metadata_lookup`
-- 

CREATE TABLE metadata_lookup (
  id int(11) NOT NULL default '0',
  document_field_id int(11) NOT NULL default '0',
  name char(255) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `mime_types`
-- 

CREATE TABLE mime_types (
  id int(11) NOT NULL default '0',
  filetypes char(100) NOT NULL default '',
  mimetypes char(100) NOT NULL default '',
  icon_path char(255) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `news`
-- 

CREATE TABLE news (
  id int(11) NOT NULL default '0',
  synopsis varchar(255) NOT NULL default '',
  body text,
  rank int(11) default NULL,
  image text,
  image_size int(11) default NULL,
  image_mime_type_id int(11) default NULL,
  active tinyint(1) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `organisations_lookup`
-- 

CREATE TABLE organisations_lookup (
  id int(11) NOT NULL default '0',
  name char(100) NOT NULL default '',
  UNIQUE KEY id (id),
  UNIQUE KEY name (name)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `roles`
-- 

CREATE TABLE roles (
  id int(11) NOT NULL default '0',
  name char(255) NOT NULL default '',
  active tinyint(1) NOT NULL default '0',
  can_read tinyint(1) NOT NULL default '0',
  can_write tinyint(1) NOT NULL default '0',
  UNIQUE KEY id (id),
  UNIQUE KEY name (name)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `search_document_user_link`
-- 

CREATE TABLE search_document_user_link (
  document_id int(11) default NULL,
  user_id int(11) default NULL,
  KEY fk_user_id (user_id),
  KEY fk_document_ids (document_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `status_lookup`
-- 

CREATE TABLE status_lookup (
  id int(11) NOT NULL default '0',
  name char(255) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `system_settings`
-- 

CREATE TABLE system_settings (
  id int(11) NOT NULL default '0',
  name char(255) NOT NULL default '',
  value char(255) NOT NULL default '',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `time_period`
-- 

CREATE TABLE time_period (
  id int(11) NOT NULL default '0',
  time_unit_id int(11) default NULL,
  units int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `time_unit_lookup`
-- 

CREATE TABLE time_unit_lookup (
  id int(11) NOT NULL default '0',
  name char(100) default NULL,
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `units_lookup`
-- 

CREATE TABLE units_lookup (
  id int(11) NOT NULL default '0',
  name char(100) NOT NULL default '',
  UNIQUE KEY id (id),
  UNIQUE KEY name (name)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `units_organisations_link`
-- 

CREATE TABLE units_organisations_link (
  id int(11) NOT NULL default '0',
  unit_id int(11) NOT NULL default '0',
  organisation_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id),
  KEY fk_unit_id (unit_id),
  KEY fk_organisation_id (organisation_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `upgrades`
-- 

CREATE TABLE upgrades (
  id int(10) unsigned NOT NULL default '0',
  descriptor char(100) NOT NULL default '',
  description char(255) NOT NULL default '',
  date_performed datetime NOT NULL default '0000-00-00 00:00:00',
  result tinyint(4) NOT NULL default '0',
  parent char(100) default NULL,
  PRIMARY KEY  (id),
  KEY descriptor (descriptor),
  KEY parent (parent)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE users (
  id int(11) NOT NULL default '0',
  username char(255) NOT NULL default '',
  name char(255) NOT NULL default '',
  password char(255) NOT NULL default '',
  quota_max int(11) NOT NULL default '0',
  quota_current int(11) NOT NULL default '0',
  email char(255) default NULL,
  mobile char(255) default NULL,
  email_notification tinyint(1) NOT NULL default '0',
  sms_notification tinyint(1) NOT NULL default '0',
  ldap_dn char(255) default NULL,
  max_sessions int(11) default NULL,
  language_id int(11) default NULL,
  UNIQUE KEY id (id),
  UNIQUE KEY username (username)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `users_groups_link`
-- 

CREATE TABLE users_groups_link (
  id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  group_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id),
  KEY fk_user_id (user_id),
  KEY fk_group_id (group_id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `web_documents`
-- 

CREATE TABLE web_documents (
  id int(11) NOT NULL default '0',
  document_id int(11) NOT NULL default '0',
  web_site_id int(11) NOT NULL default '0',
  unit_id int(11) NOT NULL default '0',
  status_id int(11) NOT NULL default '0',
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `web_documents_status_lookup`
-- 

CREATE TABLE web_documents_status_lookup (
  id int(11) NOT NULL default '0',
  name char(50) NOT NULL default '',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `web_sites`
-- 

CREATE TABLE web_sites (
  id int(11) NOT NULL default '0',
  web_site_name char(100) NOT NULL default '',
  web_site_url char(50) NOT NULL default '',
  web_master_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_active_sessions`
-- 

CREATE TABLE zseq_active_sessions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_archive_restoration_request`
-- 

CREATE TABLE zseq_archive_restoration_request (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_archiving_settings`
-- 

CREATE TABLE zseq_archiving_settings (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_archiving_type_lookup`
-- 

CREATE TABLE zseq_archiving_type_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_browse_criteria`
-- 

CREATE TABLE zseq_browse_criteria (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_data_types`
-- 

CREATE TABLE zseq_data_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_dependant_document_instance`
-- 

CREATE TABLE zseq_dependant_document_instance (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_dependant_document_template`
-- 

CREATE TABLE zseq_dependant_document_template (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_discussion_comments`
-- 

CREATE TABLE zseq_discussion_comments (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_discussion_threads`
-- 

CREATE TABLE zseq_discussion_threads (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_archiving_link`
-- 

CREATE TABLE zseq_document_archiving_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_fields`
-- 

CREATE TABLE zseq_document_fields (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_fields_link`
-- 

CREATE TABLE zseq_document_fields_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_link`
-- 

CREATE TABLE zseq_document_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_link_types`
-- 

CREATE TABLE zseq_document_link_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_subscriptions`
-- 

CREATE TABLE zseq_document_subscriptions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_transaction_types_lookup`
-- 

CREATE TABLE zseq_document_transaction_types_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_transactions`
-- 

CREATE TABLE zseq_document_transactions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_type_fields_link`
-- 

CREATE TABLE zseq_document_type_fields_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_types_lookup`
-- 

CREATE TABLE zseq_document_types_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_documents`
-- 

CREATE TABLE zseq_documents (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folder_doctypes_link`
-- 

CREATE TABLE zseq_folder_doctypes_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folder_subscriptions`
-- 

CREATE TABLE zseq_folder_subscriptions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folders`
-- 

CREATE TABLE zseq_folders (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folders_users_roles_link`
-- 

CREATE TABLE zseq_folders_users_roles_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_folders_approval_link`
-- 

CREATE TABLE zseq_groups_folders_approval_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_folders_link`
-- 

CREATE TABLE zseq_groups_folders_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_lookup`
-- 

CREATE TABLE zseq_groups_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_units_link`
-- 

CREATE TABLE zseq_groups_units_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_help`
-- 

CREATE TABLE zseq_help (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_links`
-- 

CREATE TABLE zseq_links (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_metadata_lookup`
-- 

CREATE TABLE zseq_metadata_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_mime_types`
-- 

CREATE TABLE zseq_mime_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_news`
-- 

CREATE TABLE zseq_news (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_organisations_lookup`
-- 

CREATE TABLE zseq_organisations_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_roles`
-- 

CREATE TABLE zseq_roles (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_search_document_user_link`
-- 

CREATE TABLE zseq_search_document_user_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_status_lookup`
-- 

CREATE TABLE zseq_status_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_system_settings`
-- 

CREATE TABLE zseq_system_settings (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_time_period`
-- 

CREATE TABLE zseq_time_period (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_time_unit_lookup`
-- 

CREATE TABLE zseq_time_unit_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_units_lookup`
-- 

CREATE TABLE zseq_units_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_units_organisations_link`
-- 

CREATE TABLE zseq_units_organisations_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_upgrades`
-- 

CREATE TABLE zseq_upgrades (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_users`
-- 

CREATE TABLE zseq_users (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_users_groups_link`
-- 

CREATE TABLE zseq_users_groups_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_web_documents`
-- 

CREATE TABLE zseq_web_documents (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_web_documents_status_lookup`
-- 

CREATE TABLE zseq_web_documents_status_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_web_sites`
-- 

CREATE TABLE zseq_web_sites (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=InnoDB;
