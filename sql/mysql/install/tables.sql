-- phpMyAdmin SQL Dump
-- version 2.6.0-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Dec 01, 2004 at 03:14 PM
-- Server version: 4.0.22
-- PHP Version: 4.3.9-1
-- 
-- Database: `pristinedms`
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
  id int(11) NOT NULL default '0',
  document_id int(11) default NULL,
  document_text mediumtext,
  UNIQUE KEY id (id),
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
  UNIQUE KEY id (id),
  KEY fk_document_type_id (document_type_id),
  KEY fk_creator_id (creator_id),
  KEY fk_folder_id (folder_id),
  KEY fk_checked_out_user_id (checked_out_user_id),
  KEY fk_status_id (status_id)
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
  UNIQUE KEY id (id),
  KEY fk_parent_id (parent_id),
  KEY fk_creator_id (creator_id),
  KEY fk_unit_id (unit_id)
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
  id int(11) NOT NULL default '0',
  document_id int(11) default NULL,
  user_id int(11) default NULL,
  UNIQUE KEY id (id),
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
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_archive_restoration_request`
-- 

CREATE TABLE zseq_archive_restoration_request (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_archiving_settings`
-- 

CREATE TABLE zseq_archiving_settings (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_archiving_type_lookup`
-- 

CREATE TABLE zseq_archiving_type_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_data_types`
-- 

CREATE TABLE zseq_data_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_dependant_document_instance`
-- 

CREATE TABLE zseq_dependant_document_instance (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_dependant_document_template`
-- 

CREATE TABLE zseq_dependant_document_template (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_discussion_comments`
-- 

CREATE TABLE zseq_discussion_comments (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_discussion_threads`
-- 

CREATE TABLE zseq_discussion_threads (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_archiving_link`
-- 

CREATE TABLE zseq_document_archiving_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_fields`
-- 

CREATE TABLE zseq_document_fields (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_fields_link`
-- 

CREATE TABLE zseq_document_fields_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_link`
-- 

CREATE TABLE zseq_document_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_subscriptions`
-- 

CREATE TABLE zseq_document_subscriptions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_text`
-- 

CREATE TABLE zseq_document_text (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_transaction_types_lookup`
-- 

CREATE TABLE zseq_document_transaction_types_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_transactions`
-- 

CREATE TABLE zseq_document_transactions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_type_fields_link`
-- 

CREATE TABLE zseq_document_type_fields_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_document_types_lookup`
-- 

CREATE TABLE zseq_document_types_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_documents`
-- 

CREATE TABLE zseq_documents (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folder_doctypes_link`
-- 

CREATE TABLE zseq_folder_doctypes_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folder_subscriptions`
-- 

CREATE TABLE zseq_folder_subscriptions (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folders`
-- 

CREATE TABLE zseq_folders (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_folders_users_roles_link`
-- 

CREATE TABLE zseq_folders_users_roles_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_folders_approval_link`
-- 

CREATE TABLE zseq_groups_folders_approval_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_folders_link`
-- 

CREATE TABLE zseq_groups_folders_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_lookup`
-- 

CREATE TABLE zseq_groups_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_groups_units_link`
-- 

CREATE TABLE zseq_groups_units_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_help`
-- 

CREATE TABLE zseq_help (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_links`
-- 

CREATE TABLE zseq_links (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_metadata_lookup`
-- 

CREATE TABLE zseq_metadata_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_mime_types`
-- 

CREATE TABLE zseq_mime_types (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_news`
-- 

CREATE TABLE zseq_news (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_organisations_lookup`
-- 

CREATE TABLE zseq_organisations_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_roles`
-- 

CREATE TABLE zseq_roles (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_search_document_user_link`
-- 

CREATE TABLE zseq_search_document_user_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_status_lookup`
-- 

CREATE TABLE zseq_status_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_system_settings`
-- 

CREATE TABLE zseq_system_settings (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_time_period`
-- 

CREATE TABLE zseq_time_period (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_time_unit_lookup`
-- 

CREATE TABLE zseq_time_unit_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_units_lookup`
-- 

CREATE TABLE zseq_units_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_units_organisations_link`
-- 

CREATE TABLE zseq_units_organisations_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_users`
-- 

CREATE TABLE zseq_users (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_users_groups_link`
-- 

CREATE TABLE zseq_users_groups_link (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_web_documents`
-- 

CREATE TABLE zseq_web_documents (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_web_documents_status_lookup`
-- 

CREATE TABLE zseq_web_documents_status_lookup (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `zseq_web_sites`
-- 

CREATE TABLE zseq_web_sites (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- 
-- Dumping data for table `active_sessions`
-- 


-- 
-- Dumping data for table `archive_restoration_request`
-- 


-- 
-- Dumping data for table `archiving_settings`
-- 


-- 
-- Dumping data for table `archiving_type_lookup`
-- 

INSERT INTO archiving_type_lookup VALUES (1, 'Date');
INSERT INTO archiving_type_lookup VALUES (2, 'Utilisation');

-- 
-- Dumping data for table `data_types`
-- 

INSERT INTO data_types VALUES (1, 'STRING');
INSERT INTO data_types VALUES (2, 'CHAR');
INSERT INTO data_types VALUES (3, 'TEXT');
INSERT INTO data_types VALUES (4, 'INT');
INSERT INTO data_types VALUES (5, 'FLOAT');

-- 
-- Dumping data for table `dependant_document_instance`
-- 


-- 
-- Dumping data for table `dependant_document_template`
-- 


-- 
-- Dumping data for table `discussion_comments`
-- 


-- 
-- Dumping data for table `discussion_threads`
-- 


-- 
-- Dumping data for table `document_archiving_link`
-- 


-- 
-- Dumping data for table `document_fields`
-- 

INSERT INTO document_fields VALUES (1, 'Category', 'STRING', 1, NULL);

-- 
-- Dumping data for table `document_fields_link`
-- 


-- 
-- Dumping data for table `document_link`
-- 


-- 
-- Dumping data for table `document_subscriptions`
-- 


-- 
-- Dumping data for table `document_text`
-- 


-- 
-- Dumping data for table `document_transaction_types_lookup`
-- 

INSERT INTO document_transaction_types_lookup VALUES (1, 'Create');
INSERT INTO document_transaction_types_lookup VALUES (2, 'Update');
INSERT INTO document_transaction_types_lookup VALUES (3, 'Delete');
INSERT INTO document_transaction_types_lookup VALUES (4, 'Rename');
INSERT INTO document_transaction_types_lookup VALUES (5, 'Move');
INSERT INTO document_transaction_types_lookup VALUES (6, 'Download');
INSERT INTO document_transaction_types_lookup VALUES (7, 'Check In');
INSERT INTO document_transaction_types_lookup VALUES (8, 'Check Out');
INSERT INTO document_transaction_types_lookup VALUES (9, 'Collaboration Step Rollback');
INSERT INTO document_transaction_types_lookup VALUES (10, 'View');
INSERT INTO document_transaction_types_lookup VALUES (11, 'Expunge');
INSERT INTO document_transaction_types_lookup VALUES (12, 'Force CheckIn');
INSERT INTO document_transaction_types_lookup VALUES (13, 'Email Link');
INSERT INTO document_transaction_types_lookup VALUES (14, 'Collaboration Step Approve');

-- 
-- Dumping data for table `document_transactions`
-- 


-- 
-- Dumping data for table `document_type_fields_link`
-- 


-- 
-- Dumping data for table `document_types_lookup`
-- 

INSERT INTO document_types_lookup VALUES (1, 'Default');

-- 
-- Dumping data for table `documents`
-- 


-- 
-- Dumping data for table `folder_doctypes_link`
-- 

INSERT INTO folder_doctypes_link VALUES (1, 2, 1);

-- 
-- Dumping data for table `folder_subscriptions`
-- 


-- 
-- Dumping data for table `folders`
-- 

INSERT INTO folders VALUES (1, 'Root Folder', 'Root Document Folder', 0, 1, 0, 0, NULL, NULL, NULL);
INSERT INTO folders VALUES (2, 'Default Unit', 'Default Unit Root Folder', 1, 1, 1, 0, '1', 'Root Folder', NULL);

-- 
-- Dumping data for table `folders_users_roles_link`
-- 


-- 
-- Dumping data for table `groups_folders_approval_link`
-- 


-- 
-- Dumping data for table `groups_folders_link`
-- 


-- 
-- Dumping data for table `groups_lookup`
-- 

INSERT INTO groups_lookup VALUES (1, 'System Administrators', 1, 0);
INSERT INTO groups_lookup VALUES (2, 'Unit Administrators', 0, 1);
INSERT INTO groups_lookup VALUES (3, 'Anonymous', 0, 0);

-- 
-- Dumping data for table `groups_units_link`
-- 

INSERT INTO groups_units_link VALUES (1, 2, 1);

-- 
-- Dumping data for table `help`
-- 

INSERT INTO help VALUES (1, 'browse', 'dochelp.html');
INSERT INTO help VALUES (2, 'dashboard', 'dashboardHelp.html');
INSERT INTO help VALUES (3, 'addFolder', 'addFolderHelp.html');
INSERT INTO help VALUES (4, 'editFolder', 'editFolderHelp.html');
INSERT INTO help VALUES (5, 'addFolderCollaboration', 'addFolderCollaborationHelp.html');
INSERT INTO help VALUES (6, 'modifyFolderCollaboration', 'addFolderCollaborationHelp.html');
INSERT INTO help VALUES (7, 'addDocument', 'addDocumentHelp.html');
INSERT INTO help VALUES (8, 'viewDocument', 'viewDocumentHelp.html');
INSERT INTO help VALUES (9, 'modifyDocument', 'modifyDocumentHelp.html');
INSERT INTO help VALUES (10, 'modifyDocumentRouting', 'modifyDocumentRoutingHelp.html');
INSERT INTO help VALUES (11, 'emailDocument', 'emailDocumentHelp.html');
INSERT INTO help VALUES (12, 'deleteDocument', 'deleteDocumentHelp.html');
INSERT INTO help VALUES (13, 'administration', 'administrationHelp.html');
INSERT INTO help VALUES (14, 'addGroup', 'addGroupHelp.html');
INSERT INTO help VALUES (15, 'editGroup', 'editGroupHelp.html');
INSERT INTO help VALUES (16, 'removeGroup', 'removeGroupHelp.html');
INSERT INTO help VALUES (17, 'assignGroupToUnit', 'assignGroupToUnitHelp.html');
INSERT INTO help VALUES (18, 'removeGroupFromUnit', 'removeGroupFromUnitHelp.html');
INSERT INTO help VALUES (19, 'addUnit', 'addUnitHelp.html');
INSERT INTO help VALUES (20, 'editUnit', 'editUnitHelp.html');
INSERT INTO help VALUES (21, 'removeUnit', 'removeUnitHelp.html');
INSERT INTO help VALUES (22, 'addOrg', 'addOrgHelp.html');
INSERT INTO help VALUES (23, 'editOrg', 'editOrgHelp.html');
INSERT INTO help VALUES (24, 'removeOrg', 'removeOrgHelp.html');
INSERT INTO help VALUES (25, 'addRole', 'addRoleHelp.html');
INSERT INTO help VALUES (26, 'editRole', 'editRoleHelp.html');
INSERT INTO help VALUES (27, 'removeRole', 'removeRoleHelp.html');
INSERT INTO help VALUES (28, 'addLink', 'addLinkHelp.html');
INSERT INTO help VALUES (29, 'addLinkSuccess', 'addLinkHelp.html');
INSERT INTO help VALUES (30, 'editLink', 'editLinkHelp.html');
INSERT INTO help VALUES (31, 'removeLink', 'removeLinkHelp.html');
INSERT INTO help VALUES (32, 'systemAdministration', 'systemAdministrationHelp.html');
INSERT INTO help VALUES (33, 'deleteFolder', 'deleteFolderHelp.html');
INSERT INTO help VALUES (34, 'editDocType', 'editDocTypeHelp.html');
INSERT INTO help VALUES (35, 'removeDocType', 'removeDocTypeHelp.html');
INSERT INTO help VALUES (36, 'addDocType', 'addDocTypeHelp.html');
INSERT INTO help VALUES (37, 'addDocTypeSuccess', 'addDocTypeHelp.html');
INSERT INTO help VALUES (38, 'manageSubscriptions', 'manageSubscriptionsHelp.html');
INSERT INTO help VALUES (39, 'addSubscription', 'addSubscriptionHelp.html');
INSERT INTO help VALUES (40, 'removeSubscription', 'removeSubscriptionHelp.html');
INSERT INTO help VALUES (41, 'preferences', 'preferencesHelp.html');
INSERT INTO help VALUES (42, 'editPrefsSuccess', 'preferencesHelp.html');
INSERT INTO help VALUES (43, 'modifyDocumentGenericMetaData', 'modifyDocumentGenericMetaDataHelp.html');
INSERT INTO help VALUES (44, 'viewHistory', 'viewHistoryHelp.html');
INSERT INTO help VALUES (45, 'checkInDocument', 'checkInDocumentHelp.html');
INSERT INTO help VALUES (46, 'checkOutDocument', 'checkOutDocumentHelp.html');
INSERT INTO help VALUES (47, 'advancedSearch', 'advancedSearchHelp.html');
INSERT INTO help VALUES (48, 'deleteFolderCollaboration', 'deleteFolderCollaborationHelp.html');
INSERT INTO help VALUES (49, 'addFolderDocType', 'addFolderDocTypeHelp.html');
INSERT INTO help VALUES (50, 'deleteFolderDocType', 'deleteFolderDocTypeHelp.html');
INSERT INTO help VALUES (51, 'addGroupFolderLink', 'addGroupFolderLinkHelp.html');
INSERT INTO help VALUES (52, 'deleteGroupFolderLink', 'deleteGroupFolderLinkHelp.html');
INSERT INTO help VALUES (53, 'addWebsite', 'addWebsiteHelp.html');
INSERT INTO help VALUES (54, 'addWebsiteSuccess', 'addWebsiteHelp.html');
INSERT INTO help VALUES (55, 'editWebsite', 'editWebsiteHelp.html');
INSERT INTO help VALUES (56, 'removeWebSite', 'removeWebSiteHelp.html');
INSERT INTO help VALUES (57, 'standardSearch', 'standardSearchHelp.html');
INSERT INTO help VALUES (58, 'modifyDocumentTypeMetaData', 'modifyDocumentTypeMetaDataHelp.html');
INSERT INTO help VALUES (59, 'addDocField', 'addDocFieldHelp.html');
INSERT INTO help VALUES (60, 'editDocField', 'editDocFieldHelp.html');
INSERT INTO help VALUES (61, 'removeDocField', 'removeDocFieldHelp.html');
INSERT INTO help VALUES (62, 'addMetaData', 'addMetaDataHelp.html');
INSERT INTO help VALUES (63, 'editMetaData', 'editMetaDataHelp.html');
INSERT INTO help VALUES (64, 'removeMetaData', 'removeMetaDataHelp.html');
INSERT INTO help VALUES (65, 'addUser', 'addUserHelp.html');
INSERT INTO help VALUES (66, 'editUser', 'editUserHelp.html');
INSERT INTO help VALUES (67, 'removeUser', 'removeUserHelp.html');
INSERT INTO help VALUES (68, 'addUserToGroup', 'addUserToGroupHelp.html');
INSERT INTO help VALUES (69, 'removeUserFromGroup', 'removeUserFromGroupHelp.html');
INSERT INTO help VALUES (70, 'viewDiscussion', 'viewDiscussionThread.html');
INSERT INTO help VALUES (71, 'addComment', 'addDiscussionComment.html');
INSERT INTO help VALUES (72, 'listNews', 'listDashboardNewsHelp.html');
INSERT INTO help VALUES (73, 'editNews', 'editDashboardNewsHelp.html');
INSERT INTO help VALUES (74, 'previewNews', 'previewDashboardNewsHelp.html');
INSERT INTO help VALUES (75, 'addNews', 'addDashboardNewsHelp.html');
INSERT INTO help VALUES (76, 'modifyDocumentArchiveSettings', 'modifyDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (77, 'addDocumentArchiveSettings', 'addDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (78, 'listDocFields', 'listDocumentFieldsAdmin.html');
INSERT INTO help VALUES (79, 'editDocFieldLookups', 'editDocFieldLookups.html');
INSERT INTO help VALUES (80, 'addMetaDataForField', 'addMetaDataForField.html');
INSERT INTO help VALUES (81, 'editMetaDataForField', 'editMetaDataForField.html');
INSERT INTO help VALUES (82, 'removeMetaDataFromField', 'removeMetaDataFromField.html');
INSERT INTO help VALUES (83, 'listDocs', 'listDocumentsCheckoutHelp.html');
INSERT INTO help VALUES (84, 'editDocCheckout', 'editDocCheckoutHelp.html');
INSERT INTO help VALUES (85, 'listDocTypes', 'listDocTypesHelp.html');
INSERT INTO help VALUES (86, 'editDocTypeFields', 'editDocFieldHelp.html');
INSERT INTO help VALUES (87, 'addDocTypeFieldsLink', 'addDocTypeFieldHelp.html');
INSERT INTO help VALUES (88, 'listGroups', 'listGroupsHelp.html');
INSERT INTO help VALUES (89, 'editGroupUnit', 'editGroupUnitHelp.html');
INSERT INTO help VALUES (90, 'listOrg', 'listOrgHelp.html');
INSERT INTO help VALUES (91, 'listRole', 'listRolesHelp.html');
INSERT INTO help VALUES (92, 'listUnits', 'listUnitHelp.html');
INSERT INTO help VALUES (93, 'editUnitOrg', 'editUnitOrgHelp.html');
INSERT INTO help VALUES (94, 'removeUnitFromOrg', 'removeUnitFromOrgHelp.html');
INSERT INTO help VALUES (95, 'addUnitToOrg', 'addUnitToOrgHelp.html');
INSERT INTO help VALUES (96, 'listUsers', 'listUsersHelp.html');
INSERT INTO help VALUES (97, 'editUserGroups', 'editUserGroupsHelp.html');
INSERT INTO help VALUES (98, 'listWebsites', 'listWebsitesHelp.html');

-- 
-- Dumping data for table `links`
-- 


-- 
-- Dumping data for table `metadata_lookup`
-- 


-- 
-- Dumping data for table `mime_types`
-- 

INSERT INTO mime_types VALUES (1, 'ai', 'application/postscript', NULL);
INSERT INTO mime_types VALUES (2, 'aif', 'audio/x-aiff', NULL);
INSERT INTO mime_types VALUES (3, 'aifc', 'audio/x-aiff', NULL);
INSERT INTO mime_types VALUES (4, 'aiff', 'audio/x-aiff', NULL);
INSERT INTO mime_types VALUES (5, 'asc', 'text/plain', 'icons/txt.gif');
INSERT INTO mime_types VALUES (6, 'au', 'audio/basic', NULL);
INSERT INTO mime_types VALUES (7, 'avi', 'video/x-msvideo', NULL);
INSERT INTO mime_types VALUES (8, 'bcpio', 'application/x-bcpio', NULL);
INSERT INTO mime_types VALUES (9, 'bin', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (10, 'bmp', 'image/bmp', 'icons/bmp.gif');
INSERT INTO mime_types VALUES (11, 'cdf', 'application/x-netcdf', NULL);
INSERT INTO mime_types VALUES (12, 'class', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (13, 'cpio', 'application/x-cpio', NULL);
INSERT INTO mime_types VALUES (14, 'cpt', 'application/mac-compactpro', NULL);
INSERT INTO mime_types VALUES (15, 'csh', 'application/x-csh', NULL);
INSERT INTO mime_types VALUES (16, 'css', 'text/css', NULL);
INSERT INTO mime_types VALUES (17, 'dcr', 'application/x-director', NULL);
INSERT INTO mime_types VALUES (18, 'dir', 'application/x-director', NULL);
INSERT INTO mime_types VALUES (19, 'dms', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (20, 'doc', 'application/msword', 'icons/word.gif');
INSERT INTO mime_types VALUES (21, 'dvi', 'application/x-dvi', NULL);
INSERT INTO mime_types VALUES (22, 'dxr', 'application/x-director', NULL);
INSERT INTO mime_types VALUES (23, 'eps', 'application/postscript', NULL);
INSERT INTO mime_types VALUES (24, 'etx', 'text/x-setext', NULL);
INSERT INTO mime_types VALUES (25, 'exe', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (26, 'ez', 'application/andrew-inset', NULL);
INSERT INTO mime_types VALUES (27, 'gif', 'image/gif', 'icons/gif.gif');
INSERT INTO mime_types VALUES (28, 'gtar', 'application/x-gtar', NULL);
INSERT INTO mime_types VALUES (29, 'hdf', 'application/x-hdf', NULL);
INSERT INTO mime_types VALUES (30, 'hqx', 'application/mac-binhex40', NULL);
INSERT INTO mime_types VALUES (31, 'htm', 'text/html', 'icons/html.gif');
INSERT INTO mime_types VALUES (32, 'html', 'text/html', 'icons/html.gif');
INSERT INTO mime_types VALUES (33, 'ice', 'x-conference/x-cooltalk', NULL);
INSERT INTO mime_types VALUES (34, 'ief', 'image/ief', NULL);
INSERT INTO mime_types VALUES (35, 'iges', 'model/iges', NULL);
INSERT INTO mime_types VALUES (36, 'igs', 'model/iges', NULL);
INSERT INTO mime_types VALUES (37, 'jpe', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types VALUES (38, 'jpeg', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types VALUES (39, 'jpg', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types VALUES (40, 'js', 'application/x-javascript', NULL);
INSERT INTO mime_types VALUES (41, 'kar', 'audio/midi', NULL);
INSERT INTO mime_types VALUES (42, 'latex', 'application/x-latex', NULL);
INSERT INTO mime_types VALUES (43, 'lha', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (44, 'lzh', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (45, 'man', 'application/x-troff-man', NULL);
INSERT INTO mime_types VALUES (46, 'mdb', 'application/access', 'icons/access.gif');
INSERT INTO mime_types VALUES (47, 'mdf', 'application/access', 'icons/access.gif');
INSERT INTO mime_types VALUES (48, 'me', 'application/x-troff-me', NULL);
INSERT INTO mime_types VALUES (49, 'mesh', 'model/mesh', NULL);
INSERT INTO mime_types VALUES (50, 'mid', 'audio/midi', NULL);
INSERT INTO mime_types VALUES (51, 'midi', 'audio/midi', NULL);
INSERT INTO mime_types VALUES (52, 'mif', 'application/vnd.mif', NULL);
INSERT INTO mime_types VALUES (53, 'mov', 'video/quicktime', NULL);
INSERT INTO mime_types VALUES (54, 'movie', 'video/x-sgi-movie', NULL);
INSERT INTO mime_types VALUES (55, 'mp2', 'audio/mpeg', NULL);
INSERT INTO mime_types VALUES (56, 'mp3', 'audio/mpeg', NULL);
INSERT INTO mime_types VALUES (57, 'mpe', 'video/mpeg', NULL);
INSERT INTO mime_types VALUES (58, 'mpeg', 'video/mpeg', NULL);
INSERT INTO mime_types VALUES (59, 'mpg', 'video/mpeg', NULL);
INSERT INTO mime_types VALUES (60, 'mpga', 'audio/mpeg', NULL);
INSERT INTO mime_types VALUES (61, 'mpp', 'application/vnd.ms-project', 'icons/project.gif');
INSERT INTO mime_types VALUES (62, 'ms', 'application/x-troff-ms', NULL);
INSERT INTO mime_types VALUES (63, 'msh', 'model/mesh', NULL);
INSERT INTO mime_types VALUES (64, 'nc', 'application/x-netcdf', NULL);
INSERT INTO mime_types VALUES (65, 'oda', 'application/oda', NULL);
INSERT INTO mime_types VALUES (66, 'pbm', 'image/x-portable-bitmap', NULL);
INSERT INTO mime_types VALUES (67, 'pdb', 'chemical/x-pdb', NULL);
INSERT INTO mime_types VALUES (68, 'pdf', 'application/pdf', 'icons/pdf.gif');
INSERT INTO mime_types VALUES (69, 'pgm', 'image/x-portable-graymap', NULL);
INSERT INTO mime_types VALUES (70, 'pgn', 'application/x-chess-pgn', NULL);
INSERT INTO mime_types VALUES (71, 'png', 'image/png', NULL);
INSERT INTO mime_types VALUES (72, 'pnm', 'image/x-portable-anymap', NULL);
INSERT INTO mime_types VALUES (73, 'ppm', 'image/x-portable-pixmap', NULL);
INSERT INTO mime_types VALUES (74, 'ppt', 'application/vnd.ms-powerpoint', 'icons/powerp.gif');
INSERT INTO mime_types VALUES (75, 'ps', 'application/postscript', NULL);
INSERT INTO mime_types VALUES (76, 'qt', 'video/quicktime', NULL);
INSERT INTO mime_types VALUES (77, 'ra', 'audio/x-realaudio', NULL);
INSERT INTO mime_types VALUES (78, 'ram', 'audio/x-pn-realaudio', NULL);
INSERT INTO mime_types VALUES (79, 'ras', 'image/x-cmu-raster', NULL);
INSERT INTO mime_types VALUES (80, 'rgb', 'image/x-rgb', NULL);
INSERT INTO mime_types VALUES (81, 'rm', 'audio/x-pn-realaudio', NULL);
INSERT INTO mime_types VALUES (82, 'roff', 'application/x-troff', NULL);
INSERT INTO mime_types VALUES (83, 'rpm', 'audio/x-pn-realaudio-plugin', NULL);
INSERT INTO mime_types VALUES (84, 'rtf', 'text/rtf', NULL);
INSERT INTO mime_types VALUES (85, 'rtx', 'text/richtext', NULL);
INSERT INTO mime_types VALUES (86, 'sgm', 'text/sgml', NULL);
INSERT INTO mime_types VALUES (87, 'sgml', 'text/sgml', NULL);
INSERT INTO mime_types VALUES (88, 'sh', 'application/x-sh', NULL);
INSERT INTO mime_types VALUES (89, 'shar', 'application/x-shar', NULL);
INSERT INTO mime_types VALUES (90, 'silo', 'model/mesh', NULL);
INSERT INTO mime_types VALUES (91, 'sit', 'application/x-stuffit', NULL);
INSERT INTO mime_types VALUES (92, 'skd', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (93, 'skm', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (94, 'skp', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (95, 'skt', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (96, 'smi', 'application/smil', NULL);
INSERT INTO mime_types VALUES (97, 'smil', 'application/smil', NULL);
INSERT INTO mime_types VALUES (98, 'snd', 'audio/basic', NULL);
INSERT INTO mime_types VALUES (99, 'spl', 'application/x-futuresplash', NULL);
INSERT INTO mime_types VALUES (100, 'src', 'application/x-wais-source', NULL);
INSERT INTO mime_types VALUES (101, 'sv4cpio', 'application/x-sv4cpio', NULL);
INSERT INTO mime_types VALUES (102, 'sv4crc', 'application/x-sv4crc', NULL);
INSERT INTO mime_types VALUES (103, 'swf', 'application/x-shockwave-flash', NULL);
INSERT INTO mime_types VALUES (104, 't', 'application/x-troff', NULL);
INSERT INTO mime_types VALUES (105, 'tar', 'application/x-tar', NULL);
INSERT INTO mime_types VALUES (106, 'tcl', 'application/x-tcl', NULL);
INSERT INTO mime_types VALUES (107, 'tex', 'application/x-tex', NULL);
INSERT INTO mime_types VALUES (108, 'texi', 'application/x-texinfo', NULL);
INSERT INTO mime_types VALUES (109, 'texinfo', 'application/x-texinfo', NULL);
INSERT INTO mime_types VALUES (110, 'tif', 'image/tiff', 'icons/tiff.gif');
INSERT INTO mime_types VALUES (111, 'tiff', 'image/tiff', 'icons/tiff.gif');
INSERT INTO mime_types VALUES (112, 'tr', 'application/x-troff', NULL);
INSERT INTO mime_types VALUES (113, 'tsv', 'text/tab-separated-values', NULL);
INSERT INTO mime_types VALUES (114, 'txt', 'text/plain', 'icons/txt.gif');
INSERT INTO mime_types VALUES (115, 'ustar', 'application/x-ustar', NULL);
INSERT INTO mime_types VALUES (116, 'vcd', 'application/x-cdlink', NULL);
INSERT INTO mime_types VALUES (117, 'vrml', 'model/vrml', NULL);
INSERT INTO mime_types VALUES (118, 'vsd', 'application/vnd.visio', 'icons/visio.gif');
INSERT INTO mime_types VALUES (119, 'wav', 'audio/x-wav', NULL);
INSERT INTO mime_types VALUES (120, 'wrl', 'model/vrml', NULL);
INSERT INTO mime_types VALUES (121, 'xbm', 'image/x-xbitmap', NULL);
INSERT INTO mime_types VALUES (122, 'xls', 'application/vnd.ms-excel', 'icons/excel.gif');
INSERT INTO mime_types VALUES (123, 'xml', 'text/xml', NULL);
INSERT INTO mime_types VALUES (124, 'xpm', 'image/x-xpixmap', NULL);
INSERT INTO mime_types VALUES (125, 'xwd', 'image/x-xwindowdump', NULL);
INSERT INTO mime_types VALUES (126, 'xyz', 'chemical/x-pdb', NULL);
INSERT INTO mime_types VALUES (127, 'zip', 'application/zip', 'icons/zip.gif');
INSERT INTO mime_types VALUES (128, 'gz', 'application/x-gzip', 'icons/zip.gif');
INSERT INTO mime_types VALUES (129, 'tgz', 'application/x-gzip', 'icons/zip.gif');
INSERT INTO mime_types VALUES (130, 'sxw', 'application/vnd.sun.xml.writer', 'icons/oowriter.gif');
INSERT INTO mime_types VALUES (131, 'stw', 'application/vnd.sun.xml.writer.template', 'icons/oowriter.gif');
INSERT INTO mime_types VALUES (132, 'sxc', 'application/vnd.sun.xml.calc', 'icons/oocalc.gif');
INSERT INTO mime_types VALUES (133, 'stc', 'application/vnd.sun.xml.calc.template', 'icons/oocalc.gif');
INSERT INTO mime_types VALUES (134, 'sxd', 'application/vnd.sun.xml.draw', NULL);
INSERT INTO mime_types VALUES (135, 'std', 'application/vnd.sun.xml.draw.template', NULL);
INSERT INTO mime_types VALUES (136, 'sxi', 'application/vnd.sun.xml.impress', 'icons/ooimpress.gif');
INSERT INTO mime_types VALUES (137, 'sti', 'application/vnd.sun.xml.impress.template', 'icons/ooimpress.gif');
INSERT INTO mime_types VALUES (138, 'sxg', 'application/vnd.sun.xml.writer.global', NULL);
INSERT INTO mime_types VALUES (139, 'sxm', 'application/vnd.sun.xml.math', NULL);

-- 
-- Dumping data for table `news`
-- 


-- 
-- Dumping data for table `organisations_lookup`
-- 

INSERT INTO organisations_lookup VALUES (1, 'Default Organisation');

-- 
-- Dumping data for table `roles`
-- 


-- 
-- Dumping data for table `search_document_user_link`
-- 


-- 
-- Dumping data for table `status_lookup`
-- 

INSERT INTO status_lookup VALUES (1, 'Live');
INSERT INTO status_lookup VALUES (2, 'Published');
INSERT INTO status_lookup VALUES (3, 'Deleted');
INSERT INTO status_lookup VALUES (4, 'Archived');

-- 
-- Dumping data for table `system_settings`
-- 

INSERT INTO system_settings VALUES (1, 'lastIndexUpdate', '0');
INSERT INTO system_settings VALUES (2, 'knowledgeTreeVersion', '1.2.5');

-- 
-- Dumping data for table `time_period`
-- 


-- 
-- Dumping data for table `time_unit_lookup`
-- 

INSERT INTO time_unit_lookup VALUES (1, 'Years');
INSERT INTO time_unit_lookup VALUES (2, 'Months');
INSERT INTO time_unit_lookup VALUES (3, 'Days');

-- 
-- Dumping data for table `units_lookup`
-- 

INSERT INTO units_lookup VALUES (1, 'Default Unit');

-- 
-- Dumping data for table `units_organisations_link`
-- 

INSERT INTO units_organisations_link VALUES (1, 1, 1);

-- 
-- Dumping data for table `users`
-- 

INSERT INTO users VALUES (1, 'admin', 'Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', 1, 1, '', 1, 1);
INSERT INTO users VALUES (2, 'unitAdmin', 'Unit Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', 1, 1, '', 1, 1);
INSERT INTO users VALUES (3, 'guest', 'Anonymous', '084e0343a0486ff05530df6c705c8bb4', 0, 0, '', '', 0, 0, '', 19, 1);

-- 
-- Dumping data for table `users_groups_link`
-- 

INSERT INTO users_groups_link VALUES (1, 1, 1);
INSERT INTO users_groups_link VALUES (2, 2, 2);
INSERT INTO users_groups_link VALUES (3, 3, 3);

-- 
-- Dumping data for table `web_documents`
-- 


-- 
-- Dumping data for table `web_documents_status_lookup`
-- 

INSERT INTO web_documents_status_lookup VALUES (1, 'Pending');
INSERT INTO web_documents_status_lookup VALUES (2, 'Published');
INSERT INTO web_documents_status_lookup VALUES (3, 'Not Published');

-- 
-- Dumping data for table `web_sites`
-- 


-- 
-- Dumping data for table `zseq_active_sessions`
-- 

INSERT INTO zseq_active_sessions VALUES (1);

-- 
-- Dumping data for table `zseq_archive_restoration_request`
-- 

INSERT INTO zseq_archive_restoration_request VALUES (1);

-- 
-- Dumping data for table `zseq_archiving_settings`
-- 

INSERT INTO zseq_archiving_settings VALUES (1);

-- 
-- Dumping data for table `zseq_archiving_type_lookup`
-- 

INSERT INTO zseq_archiving_type_lookup VALUES (2);

-- 
-- Dumping data for table `zseq_data_types`
-- 

INSERT INTO zseq_data_types VALUES (5);

-- 
-- Dumping data for table `zseq_dependant_document_instance`
-- 

INSERT INTO zseq_dependant_document_instance VALUES (1);

-- 
-- Dumping data for table `zseq_dependant_document_template`
-- 

INSERT INTO zseq_dependant_document_template VALUES (1);

-- 
-- Dumping data for table `zseq_discussion_comments`
-- 

INSERT INTO zseq_discussion_comments VALUES (1);

-- 
-- Dumping data for table `zseq_discussion_threads`
-- 

INSERT INTO zseq_discussion_threads VALUES (1);

-- 
-- Dumping data for table `zseq_document_archiving_link`
-- 

INSERT INTO zseq_document_archiving_link VALUES (1);

-- 
-- Dumping data for table `zseq_document_fields`
-- 

INSERT INTO zseq_document_fields VALUES (1);

-- 
-- Dumping data for table `zseq_document_fields_link`
-- 

INSERT INTO zseq_document_fields_link VALUES (1);

-- 
-- Dumping data for table `zseq_document_link`
-- 

INSERT INTO zseq_document_link VALUES (1);

-- 
-- Dumping data for table `zseq_document_subscriptions`
-- 

INSERT INTO zseq_document_subscriptions VALUES (1);

-- 
-- Dumping data for table `zseq_document_text`
-- 

INSERT INTO zseq_document_text VALUES (1);

-- 
-- Dumping data for table `zseq_document_transaction_types_lookup`
-- 

INSERT INTO zseq_document_transaction_types_lookup VALUES (14);

-- 
-- Dumping data for table `zseq_document_transactions`
-- 

INSERT INTO zseq_document_transactions VALUES (1);

-- 
-- Dumping data for table `zseq_document_type_fields_link`
-- 

INSERT INTO zseq_document_type_fields_link VALUES (1);

-- 
-- Dumping data for table `zseq_document_types_lookup`
-- 

INSERT INTO zseq_document_types_lookup VALUES (1);

-- 
-- Dumping data for table `zseq_documents`
-- 

INSERT INTO zseq_documents VALUES (1);

-- 
-- Dumping data for table `zseq_folder_doctypes_link`
-- 

INSERT INTO zseq_folder_doctypes_link VALUES (1);

-- 
-- Dumping data for table `zseq_folder_subscriptions`
-- 

INSERT INTO zseq_folder_subscriptions VALUES (1);

-- 
-- Dumping data for table `zseq_folders`
-- 

INSERT INTO zseq_folders VALUES (2);

-- 
-- Dumping data for table `zseq_folders_users_roles_link`
-- 

INSERT INTO zseq_folders_users_roles_link VALUES (1);

-- 
-- Dumping data for table `zseq_groups_folders_approval_link`
-- 

INSERT INTO zseq_groups_folders_approval_link VALUES (1);

-- 
-- Dumping data for table `zseq_groups_folders_link`
-- 

INSERT INTO zseq_groups_folders_link VALUES (1);

-- 
-- Dumping data for table `zseq_groups_lookup`
-- 

INSERT INTO zseq_groups_lookup VALUES (3);

-- 
-- Dumping data for table `zseq_groups_units_link`
-- 

INSERT INTO zseq_groups_units_link VALUES (1);

-- 
-- Dumping data for table `zseq_help`
-- 

INSERT INTO zseq_help VALUES (98);

-- 
-- Dumping data for table `zseq_links`
-- 

INSERT INTO zseq_links VALUES (1);

-- 
-- Dumping data for table `zseq_metadata_lookup`
-- 

INSERT INTO zseq_metadata_lookup VALUES (1);

-- 
-- Dumping data for table `zseq_mime_types`
-- 

INSERT INTO zseq_mime_types VALUES (139);

-- 
-- Dumping data for table `zseq_news`
-- 

INSERT INTO zseq_news VALUES (1);

-- 
-- Dumping data for table `zseq_organisations_lookup`
-- 

INSERT INTO zseq_organisations_lookup VALUES (1);

-- 
-- Dumping data for table `zseq_roles`
-- 

INSERT INTO zseq_roles VALUES (1);

-- 
-- Dumping data for table `zseq_search_document_user_link`
-- 

INSERT INTO zseq_search_document_user_link VALUES (1);

-- 
-- Dumping data for table `zseq_status_lookup`
-- 

INSERT INTO zseq_status_lookup VALUES (4);

-- 
-- Dumping data for table `zseq_system_settings`
-- 

INSERT INTO zseq_system_settings VALUES (2);

-- 
-- Dumping data for table `zseq_time_period`
-- 

INSERT INTO zseq_time_period VALUES (1);

-- 
-- Dumping data for table `zseq_time_unit_lookup`
-- 

INSERT INTO zseq_time_unit_lookup VALUES (3);

-- 
-- Dumping data for table `zseq_units_lookup`
-- 

INSERT INTO zseq_units_lookup VALUES (1);

-- 
-- Dumping data for table `zseq_units_organisations_link`
-- 

INSERT INTO zseq_units_organisations_link VALUES (1);

-- 
-- Dumping data for table `zseq_users`
-- 

INSERT INTO zseq_users VALUES (3);

-- 
-- Dumping data for table `zseq_users_groups_link`
-- 

INSERT INTO zseq_users_groups_link VALUES (3);

-- 
-- Dumping data for table `zseq_web_documents`
-- 

INSERT INTO zseq_web_documents VALUES (1);

-- 
-- Dumping data for table `zseq_web_documents_status_lookup`
-- 

INSERT INTO zseq_web_documents_status_lookup VALUES (3);

-- 
-- Dumping data for table `zseq_web_sites`
-- 

INSERT INTO zseq_web_sites VALUES (1);
