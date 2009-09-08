-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 08, 2009 at 04:07 PM
-- Server version: 5.1.31
-- PHP Version: 5.2.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dms_install`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE IF NOT EXISTS `active_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `lastused` datetime DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `apptype` varchar(15) NOT NULL DEFAULT 'webapp',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `archive_restoration_request`
--

CREATE TABLE IF NOT EXISTS `archive_restoration_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL DEFAULT '0',
  `request_user_id` int(11) NOT NULL DEFAULT '0',
  `admin_user_id` int(11) NOT NULL DEFAULT '0',
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `request_user_id` (`request_user_id`),
  KEY `admin_user_id` (`admin_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `archiving_settings`
--

CREATE TABLE IF NOT EXISTS `archiving_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `archiving_type_id` int(11) NOT NULL DEFAULT '0',
  `expiration_date` date DEFAULT NULL,
  `document_transaction_id` int(11) DEFAULT NULL,
  `time_period_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archiving_type_id` (`archiving_type_id`),
  KEY `time_period_id` (`time_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `archiving_type_lookup`
--

CREATE TABLE IF NOT EXISTS `archiving_type_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `authentication_sources`
--

CREATE TABLE IF NOT EXISTS `authentication_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `namespace` varchar(255) NOT NULL DEFAULT '',
  `authentication_provider` varchar(255) NOT NULL DEFAULT '',
  `config` mediumtext NOT NULL,
  `is_user_source` tinyint(1) NOT NULL DEFAULT '0',
  `is_group_source` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `baobab_keys`
--

CREATE TABLE IF NOT EXISTS `baobab_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_data` blob NOT NULL,
  `signature` blob NOT NULL,
  `licenses` int(11) NOT NULL,
  `expiry_date` datetime NOT NULL,
  `license_id` varchar(50) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `tier` enum('community','evaluation','basic','plus','premium') NOT NULL DEFAULT 'community',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `baobab_scan`
--

CREATE TABLE IF NOT EXISTS `baobab_scan` (
  `checkdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verify` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `baobab_user_keys`
--

CREATE TABLE IF NOT EXISTS `baobab_user_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `key_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `key_id` (`key_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `column_entries`
--

CREATE TABLE IF NOT EXISTS `column_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `column_namespace` varchar(255) NOT NULL DEFAULT '',
  `view_namespace` varchar(255) NOT NULL DEFAULT '',
  `config_array` text NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `view_namespace` (`view_namespace`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `comment_searchable_text`
--

CREATE TABLE IF NOT EXISTS `comment_searchable_text` (
  `comment_id` int(11) NOT NULL DEFAULT '0',
  `body` mediumtext,
  `document_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `document_id` (`document_id`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `config_groups`
--

CREATE TABLE IF NOT EXISTS `config_groups` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `category` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Table structure for table `config_settings`
--

CREATE TABLE IF NOT EXISTS `config_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `item` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT 'default',
  `default_value` varchar(255) NOT NULL,
  `type` enum('boolean','string','numeric_string','numeric','radio','dropdown') DEFAULT 'string',
  `options` mediumtext,
  `can_edit` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=138 ;

-- --------------------------------------------------------

--
-- Table structure for table `custom_sequences`
--

CREATE TABLE IF NOT EXISTS `custom_sequences` (
  `token` varchar(100) NOT NULL,
  `document_type_id` int(11) NOT NULL DEFAULT '0',
  `seq_no` int(11) DEFAULT '0',
  `reset_frequency` enum('monthly','yearly','never') DEFAULT 'never',
  `last_reset` date DEFAULT NULL,
  PRIMARY KEY (`token`,`document_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dashlet_disables`
--

CREATE TABLE IF NOT EXISTS `dashlet_disables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `dashlet_namespace` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `dashlet_namespace` (`dashlet_namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `data_types`
--

CREATE TABLE IF NOT EXISTS `data_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `discussion_comments`
--

CREATE TABLE IF NOT EXISTS `discussion_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `in_reply_to` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `subject` mediumtext,
  `body` mediumtext,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thread_id` (`thread_id`),
  KEY `user_id` (`user_id`),
  KEY `in_reply_to` (`in_reply_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `discussion_threads`
--

CREATE TABLE IF NOT EXISTS `discussion_threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `first_comment_id` int(11) DEFAULT NULL,
  `last_comment_id` int(11) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `replies` int(11) NOT NULL DEFAULT '0',
  `creator_id` int(11) NOT NULL,
  `close_reason` mediumtext NOT NULL,
  `close_metadata_version` int(11) NOT NULL DEFAULT '0',
  `state` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `first_comment_id` (`first_comment_id`),
  KEY `last_comment_id` (`last_comment_id`),
  KEY `creator_id` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator_id` int(11) DEFAULT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `folder_id` int(11) DEFAULT NULL,
  `is_checked_out` tinyint(1) NOT NULL DEFAULT '0',
  `parent_folder_ids` mediumtext,
  `full_path` mediumtext,
  `checked_out_user_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `permission_object_id` int(11) DEFAULT NULL,
  `permission_lookup_id` int(11) DEFAULT NULL,
  `metadata_version` int(11) NOT NULL DEFAULT '0',
  `modified_user_id` int(11) DEFAULT NULL,
  `metadata_version_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `immutable` tinyint(1) NOT NULL DEFAULT '0',
  `restore_folder_id` int(11) DEFAULT NULL,
  `restore_folder_path` text,
  `checkedout` datetime DEFAULT NULL,
  `oem_no` varchar(255) DEFAULT NULL,
  `linked_document_id` int(11) DEFAULT NULL,
  `guid` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `folder_id` (`folder_id`),
  KEY `checked_out_user_id` (`checked_out_user_id`),
  KEY `status_id` (`status_id`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `permission_lookup_id` (`permission_lookup_id`),
  KEY `modified_user_id` (`modified_user_id`),
  KEY `metadata_version_id` (`metadata_version_id`),
  KEY `created` (`created`),
  KEY `modified` (`modified`),
  KEY `full_path` (`full_path`(255)),
  KEY `immutable` (`immutable`),
  KEY `checkedout` (`checkedout`),
  KEY `oem_no` (`oem_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_alerts`
--

CREATE TABLE IF NOT EXISTS `document_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `doc_type_alert_id` int(11) DEFAULT NULL,
  `alert_date` date NOT NULL,
  `last_alert` date DEFAULT NULL,
  `comment` mediumtext NOT NULL,
  `creator_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `modifier_id` int(11) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_alerts_users`
--

CREATE TABLE IF NOT EXISTS `document_alerts_users` (
  `alert_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `member_id` int(11) NOT NULL,
  `member_type` enum('user','group','role') NOT NULL DEFAULT 'user',
  KEY `alert_id` (`alert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document_archiving_link`
--

CREATE TABLE IF NOT EXISTS `document_archiving_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL DEFAULT '0',
  `archiving_settings_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `archiving_settings_id` (`archiving_settings_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_content_version`
--

CREATE TABLE IF NOT EXISTS `document_content_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL DEFAULT '0',
  `filename` mediumtext NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT '0',
  `mime_id` int(11) DEFAULT '9',
  `major_version` int(11) NOT NULL DEFAULT '0',
  `minor_version` int(11) NOT NULL DEFAULT '0',
  `storage_path` varchar(1024) DEFAULT NULL,
  `md5hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `mime_id` (`mime_id`),
  KEY `storage_path` (`storage_path`(255)),
  KEY `filename` (`filename`(255)),
  KEY `size` (`size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_fields`
--

CREATE TABLE IF NOT EXISTS `document_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `data_type` varchar(100) NOT NULL DEFAULT '',
  `is_generic` tinyint(1) DEFAULT NULL,
  `has_lookup` tinyint(1) DEFAULT NULL,
  `has_lookuptree` tinyint(1) DEFAULT NULL,
  `parent_fieldset` int(11) DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `description` mediumtext NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `is_html` tinyint(1) DEFAULT NULL,
  `max_length` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_fieldset` (`parent_fieldset`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_fields_link`
--

CREATE TABLE IF NOT EXISTS `document_fields_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  `metadata_version_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_field_id` (`document_field_id`),
  KEY `metadata_version_id` (`metadata_version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_incomplete`
--

CREATE TABLE IF NOT EXISTS `document_incomplete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contents` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `metadata` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_link`
--

CREATE TABLE IF NOT EXISTS `document_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_document_id` int(11) NOT NULL DEFAULT '0',
  `child_document_id` int(11) NOT NULL DEFAULT '0',
  `link_type_id` int(11) NOT NULL DEFAULT '0',
  `external_url` varchar(255) DEFAULT NULL,
  `external_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_document_id` (`parent_document_id`),
  KEY `child_document_id` (`child_document_id`),
  KEY `link_type_id` (`link_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_link_types`
--

CREATE TABLE IF NOT EXISTS `document_link_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `reverse_name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_metadata_version`
--

CREATE TABLE IF NOT EXISTS `document_metadata_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL DEFAULT '0',
  `content_version_id` int(11) NOT NULL DEFAULT '0',
  `document_type_id` int(11) NOT NULL DEFAULT '0',
  `name` mediumtext NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `metadata_version` int(11) NOT NULL DEFAULT '0',
  `version_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `version_creator_id` int(11) NOT NULL DEFAULT '0',
  `workflow_id` int(11) DEFAULT NULL,
  `workflow_state_id` int(11) DEFAULT NULL,
  `custom_doc_no` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_type_id` (`document_type_id`),
  KEY `status_id` (`status_id`),
  KEY `document_id` (`document_id`),
  KEY `version_creator_id` (`version_creator_id`),
  KEY `content_version_id` (`content_version_id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `workflow_state_id` (`workflow_state_id`),
  KEY `version_created` (`version_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_role_allocations`
--

CREATE TABLE IF NOT EXISTS `document_role_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0',
  `permission_descriptor_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`),
  KEY `document_id_role_id` (`document_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_searchable_text`
--

CREATE TABLE IF NOT EXISTS `document_searchable_text` (
  `document_id` int(11) DEFAULT NULL,
  `document_text` longtext,
  KEY `document_id` (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document_subscriptions`
--

CREATE TABLE IF NOT EXISTS `document_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `document_id` int(11) NOT NULL DEFAULT '0',
  `is_alerted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_tags`
--

CREATE TABLE IF NOT EXISTS `document_tags` (
  `document_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`document_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document_text`
--

CREATE TABLE IF NOT EXISTS `document_text` (
  `document_id` int(11) NOT NULL DEFAULT '0',
  `document_text` longtext,
  PRIMARY KEY (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document_transactions`
--

CREATE TABLE IF NOT EXISTS `document_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(15) DEFAULT NULL,
  `filename` mediumtext NOT NULL,
  `comment` mediumtext NOT NULL,
  `transaction_namespace` varchar(255) NOT NULL DEFAULT 'ktcore.transactions.event',
  `session_id` int(11) DEFAULT NULL,
  `admin_mode` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `document_id` (`document_id`),
  KEY `user_id` (`user_id`),
  KEY `datetime` (`datetime`,`transaction_namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_transaction_text`
--

CREATE TABLE IF NOT EXISTS `document_transaction_text` (
  `document_id` int(11) NOT NULL DEFAULT '0',
  `document_text` mediumtext,
  PRIMARY KEY (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document_transaction_types_lookup`
--

CREATE TABLE IF NOT EXISTS `document_transaction_types_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `namespace` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_types_lookup`
--

CREATE TABLE IF NOT EXISTS `document_types_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `scheme` varchar(100) DEFAULT NULL,
  `regen_on_checkin` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_type_alerts`
--

CREATE TABLE IF NOT EXISTS `document_type_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_type_id` int(11) NOT NULL,
  `alert_period` int(11) NOT NULL,
  `comment` mediumtext NOT NULL,
  `reset` int(11) NOT NULL DEFAULT '0',
  `repeatable` int(11) NOT NULL DEFAULT '0',
  `apply_to_all` int(11) NOT NULL DEFAULT '0',
  `creator_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `modifier_id` int(11) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `update_processed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `document_type_id` (`document_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_type_fieldsets_link`
--

CREATE TABLE IF NOT EXISTS `document_type_fieldsets_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_type_id` int(11) NOT NULL DEFAULT '0',
  `fieldset_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `document_type_id` (`document_type_id`),
  KEY `fieldset_id` (`fieldset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `document_type_fields_link`
--

CREATE TABLE IF NOT EXISTS `document_type_fields_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_type_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `document_type_id` (`document_type_id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `download_files`
--

CREATE TABLE IF NOT EXISTS `download_files` (
  `document_id` int(11) NOT NULL,
  `session` varchar(100) NOT NULL,
  `download_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `downloaded` int(10) unsigned NOT NULL DEFAULT '0',
  `filesize` int(10) unsigned NOT NULL,
  `content_version` int(10) unsigned NOT NULL,
  `hash` varchar(100) NOT NULL,
  PRIMARY KEY (`document_id`,`session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `download_queue`
--

CREATE TABLE IF NOT EXISTS `download_queue` (
  `code` char(16) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_type` enum('document','folder') NOT NULL DEFAULT 'folder',
  `user_id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `errors` mediumtext,
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fieldsets`
--

CREATE TABLE IF NOT EXISTS `fieldsets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `namespace` varchar(255) NOT NULL DEFAULT '',
  `mandatory` tinyint(1) NOT NULL DEFAULT '0',
  `is_conditional` tinyint(1) NOT NULL DEFAULT '0',
  `master_field` int(11) DEFAULT NULL,
  `is_generic` tinyint(1) NOT NULL DEFAULT '0',
  `is_complex` tinyint(1) NOT NULL DEFAULT '0',
  `is_complete` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `description` mediumtext NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `master_field` (`master_field`),
  KEY `is_generic` (`is_generic`),
  KEY `is_complete` (`is_complete`),
  KEY `is_system` (`is_system`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `field_behaviours`
--

CREATE TABLE IF NOT EXISTS `field_behaviours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `human_name` varchar(100) NOT NULL,
  `field_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `field_behaviour_options`
--

CREATE TABLE IF NOT EXISTS `field_behaviour_options` (
  `behaviour_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `instance_id` int(11) NOT NULL DEFAULT '0',
  KEY `field_id` (`field_id`),
  KEY `instance_id` (`instance_id`),
  KEY `behaviour_id_field_id` (`behaviour_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `field_orders`
--

CREATE TABLE IF NOT EXISTS `field_orders` (
  `parent_field_id` int(11) NOT NULL DEFAULT '0',
  `child_field_id` int(11) NOT NULL DEFAULT '0',
  `fieldset_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `child_field_id` (`child_field_id`),
  KEY `parent_field_id` (`parent_field_id`),
  KEY `fieldset_id` (`fieldset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `field_value_instances`
--

CREATE TABLE IF NOT EXISTS `field_value_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL DEFAULT '0',
  `field_value_id` int(11) NOT NULL DEFAULT '0',
  `behaviour_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_value_id` (`field_value_id`),
  KEY `behaviour_id` (`behaviour_id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE IF NOT EXISTS `folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(11) DEFAULT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `parent_folder_ids` mediumtext,
  `full_path` mediumtext,
  `permission_object_id` int(11) DEFAULT NULL,
  `permission_lookup_id` int(11) DEFAULT NULL,
  `restrict_document_types` tinyint(1) NOT NULL DEFAULT '0',
  `owner_id` int(11) DEFAULT NULL,
  `linked_folder_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `permission_lookup_id` (`permission_lookup_id`),
  KEY `parent_id_name` (`parent_id`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `folders_users_roles_link`
--

CREATE TABLE IF NOT EXISTS `folders_users_roles_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_folder_approval_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `document_id` int(11) NOT NULL DEFAULT '0',
  `datetime` datetime DEFAULT NULL,
  `done` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `dependant_documents_created` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `folder_descendants`
--

CREATE TABLE IF NOT EXISTS `folder_descendants` (
  `parent_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  KEY `parent_id` (`parent_id`),
  KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `folder_doctypes_link`
--

CREATE TABLE IF NOT EXISTS `folder_doctypes_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL DEFAULT '0',
  `document_type_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `document_type_id` (`document_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `folder_searchable_text`
--

CREATE TABLE IF NOT EXISTS `folder_searchable_text` (
  `folder_id` int(11) NOT NULL DEFAULT '0',
  `folder_text` mediumtext,
  PRIMARY KEY (`folder_id`),
  FULLTEXT KEY `folder_text` (`folder_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `folder_subscriptions`
--

CREATE TABLE IF NOT EXISTS `folder_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `folder_id` int(11) NOT NULL DEFAULT '0',
  `is_alerted` tinyint(1) DEFAULT NULL,
  `with_subfolders` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `folder_transactions`
--

CREATE TABLE IF NOT EXISTS `folder_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(15) DEFAULT NULL,
  `comment` varchar(255) NOT NULL,
  `transaction_namespace` varchar(255) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `admin_mode` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `folder_workflow_map`
--

CREATE TABLE IF NOT EXISTS `folder_workflow_map` (
  `folder_id` int(11) NOT NULL DEFAULT '0',
  `workflow_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`folder_id`),
  KEY `workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups_groups_link`
--

CREATE TABLE IF NOT EXISTS `groups_groups_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_group_id` int(11) NOT NULL DEFAULT '0',
  `member_group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_group_id` (`parent_group_id`),
  KEY `member_group_id` (`member_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `groups_lookup`
--

CREATE TABLE IF NOT EXISTS `groups_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `is_sys_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_unit_admin` tinyint(1) NOT NULL DEFAULT '0',
  `unit_id` int(11) DEFAULT NULL,
  `authentication_details_s2` varchar(255) DEFAULT NULL,
  `authentication_details_s1` varchar(255) DEFAULT NULL,
  `authentication_source_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `unit_id` (`unit_id`),
  KEY `authentication_source_id_authentication_details_s1` (`authentication_source_id`,`authentication_details_s1`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `help`
--

CREATE TABLE IF NOT EXISTS `help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fSection` varchar(100) NOT NULL DEFAULT '',
  `help_info` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;

-- --------------------------------------------------------

--
-- Table structure for table `help_replacement`
--

CREATE TABLE IF NOT EXISTS `help_replacement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `index_files`
--

CREATE TABLE IF NOT EXISTS `index_files` (
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `indexdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `processdate` datetime DEFAULT NULL,
  `what` char(1) DEFAULT NULL,
  `status_msg` mediumtext,
  PRIMARY KEY (`document_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `interceptor_instances`
--

CREATE TABLE IF NOT EXISTS `interceptor_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `interceptor_namespace` varchar(255) NOT NULL,
  `config` text,
  PRIMARY KEY (`id`),
  KEY `interceptor_namespace` (`interceptor_namespace`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(100) NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `metadata_lookup`
--

CREATE TABLE IF NOT EXISTS `metadata_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_field_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `treeorg_parent` int(11) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `is_stuck` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `document_field_id` (`document_field_id`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `metadata_lookup_tree`
--

CREATE TABLE IF NOT EXISTS `metadata_lookup_tree` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_field_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `metadata_lookup_tree_parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_field_id` (`document_field_id`),
  KEY `metadata_lookup_tree_parent` (`metadata_lookup_tree_parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `mime_documents`
--

CREATE TABLE IF NOT EXISTS `mime_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mime_doc` varchar(100) DEFAULT NULL,
  `icon_path` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `mime_document_mapping`
--

CREATE TABLE IF NOT EXISTS `mime_document_mapping` (
  `mime_document_id` int(11) NOT NULL,
  `mime_type_id` int(11) NOT NULL,
  PRIMARY KEY (`mime_type_id`,`mime_document_id`),
  KEY `mime_document_id` (`mime_document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mime_extractors`
--

CREATE TABLE IF NOT EXISTS `mime_extractors` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `mime_types`
--

CREATE TABLE IF NOT EXISTS `mime_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filetypes` varchar(100) NOT NULL,
  `mimetypes` varchar(100) NOT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `friendly_name` varchar(255) NOT NULL DEFAULT '',
  `extractor_id` mediumint(9) DEFAULT NULL,
  `mime_document_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mime_document_id` (`mime_document_id`),
  KEY `extractor_id` (`extractor_id`),
  KEY `filetypes` (`filetypes`),
  KEY `mimetypes` (`mimetypes`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=172 ;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `synopsis` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext,
  `rank` int(11) DEFAULT NULL,
  `image` mediumtext,
  `image_size` int(11) DEFAULT NULL,
  `image_mime_type_id` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `image_mime_type_id` (`image_mime_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `label` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data_int_1` int(11) DEFAULT NULL,
  `data_int_2` int(11) DEFAULT NULL,
  `data_str_1` varchar(255) DEFAULT NULL,
  `data_str_2` varchar(255) DEFAULT NULL,
  `data_text_1` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `data_int_1` (`data_int_1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `organisations_lookup`
--

CREATE TABLE IF NOT EXISTS `organisations_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `human_name` varchar(100) NOT NULL,
  `built_in` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `permission_assignments`
--

CREATE TABLE IF NOT EXISTS `permission_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) NOT NULL DEFAULT '0',
  `permission_object_id` int(11) NOT NULL DEFAULT '0',
  `permission_descriptor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_object_id_permission_id` (`permission_object_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `permission_descriptors`
--

CREATE TABLE IF NOT EXISTS `permission_descriptors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descriptor` varchar(32) NOT NULL DEFAULT '',
  `descriptor_text` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `descriptor` (`descriptor`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `permission_descriptor_groups`
--

CREATE TABLE IF NOT EXISTS `permission_descriptor_groups` (
  `descriptor_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`descriptor_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permission_descriptor_roles`
--

CREATE TABLE IF NOT EXISTS `permission_descriptor_roles` (
  `descriptor_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`descriptor_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permission_descriptor_users`
--

CREATE TABLE IF NOT EXISTS `permission_descriptor_users` (
  `descriptor_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`descriptor_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permission_dynamic_assignments`
--

CREATE TABLE IF NOT EXISTS `permission_dynamic_assignments` (
  `dynamic_condition_id` int(11) NOT NULL DEFAULT '0',
  `permission_id` int(11) NOT NULL DEFAULT '0',
  KEY `dynamic_condition_id` (`dynamic_condition_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permission_dynamic_conditions`
--

CREATE TABLE IF NOT EXISTS `permission_dynamic_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_object_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `condition_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `group_id` (`group_id`),
  KEY `condition_id` (`condition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `permission_lookups`
--

CREATE TABLE IF NOT EXISTS `permission_lookups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `permission_lookup_assignments`
--

CREATE TABLE IF NOT EXISTS `permission_lookup_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) NOT NULL DEFAULT '0',
  `permission_lookup_id` int(11) NOT NULL DEFAULT '0',
  `permission_descriptor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_lookup_id_permission_id` (`permission_lookup_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=65 ;

-- --------------------------------------------------------

--
-- Table structure for table `permission_objects`
--

CREATE TABLE IF NOT EXISTS `permission_objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `version` int(11) NOT NULL DEFAULT '0',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `data` mediumtext,
  `unavailable` tinyint(1) NOT NULL DEFAULT '0',
  `friendly_name` varchar(255) DEFAULT '',
  `orderby` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=48 ;

-- --------------------------------------------------------

--
-- Table structure for table `plugin_helper`
--

CREATE TABLE IF NOT EXISTS `plugin_helper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(120) NOT NULL,
  `plugin` varchar(120) NOT NULL,
  `classname` varchar(120) DEFAULT NULL,
  `pathname` varchar(255) DEFAULT NULL,
  `object` varchar(1000) NOT NULL,
  `classtype` varchar(120) NOT NULL,
  `viewtype` enum('general','dashboard','plugin','folder','document','admindispatcher','dispatcher') NOT NULL DEFAULT 'general',
  PRIMARY KEY (`id`),
  KEY `name` (`namespace`),
  KEY `parent` (`plugin`),
  KEY `view` (`viewtype`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=341 ;

-- --------------------------------------------------------

--
-- Table structure for table `plugin_rss`
--

CREATE TABLE IF NOT EXISTS `plugin_rss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quicklinks`
--

CREATE TABLE IF NOT EXISTS `quicklinks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_id` int(11) NOT NULL DEFAULT '0',
  `is_folder` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `role_allocations`
--

CREATE TABLE IF NOT EXISTS `role_allocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0',
  `permission_descriptor_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `role_id` (`role_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `saved_searches`
--

CREATE TABLE IF NOT EXISTS `saved_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `is_condition` tinyint(1) NOT NULL DEFAULT '0',
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) DEFAULT NULL,
  `search` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scheduler_tasks`
--

CREATE TABLE IF NOT EXISTS `scheduler_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task` varchar(50) NOT NULL,
  `script_url` varchar(255) NOT NULL,
  `script_params` varchar(255) DEFAULT NULL,
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  `frequency` varchar(25) DEFAULT NULL,
  `run_time` datetime DEFAULT NULL,
  `previous_run_time` datetime DEFAULT NULL,
  `run_duration` float DEFAULT NULL,
  `status` enum('enabled','disabled','system') NOT NULL DEFAULT 'disabled',
  PRIMARY KEY (`id`),
  UNIQUE KEY `task` (`task`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_document_user_link`
--

CREATE TABLE IF NOT EXISTS `search_document_user_link` (
  `document_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  KEY `document_id` (`document_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `search_ranking`
--

CREATE TABLE IF NOT EXISTS `search_ranking` (
  `groupname` varchar(100) NOT NULL,
  `itemname` varchar(100) NOT NULL,
  `ranking` float DEFAULT '0',
  `type` enum('T','M','S') DEFAULT 'T' COMMENT 'T=Table, M=Metadata, S=Searchable',
  PRIMARY KEY (`groupname`,`itemname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `search_saved`
--

CREATE TABLE IF NOT EXISTS `search_saved` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `expression` mediumtext NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('S','C','W','B') NOT NULL DEFAULT 'S' COMMENT 'S=saved search, C=permission, w=workflow, B=subscription',
  `shared` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_saved_events`
--

CREATE TABLE IF NOT EXISTS `search_saved_events` (
  `document_id` int(11) NOT NULL,
  PRIMARY KEY (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `status_lookup`
--

CREATE TABLE IF NOT EXISTS `status_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag_words`
--

CREATE TABLE IF NOT EXISTS `tag_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `time_period`
--

CREATE TABLE IF NOT EXISTS `time_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_unit_id` int(11) DEFAULT NULL,
  `units` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `time_unit_id` (`time_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `time_unit_lookup`
--

CREATE TABLE IF NOT EXISTS `time_unit_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `trigger_selection`
--

CREATE TABLE IF NOT EXISTS `trigger_selection` (
  `event_ns` varchar(255) NOT NULL DEFAULT '',
  `selection_ns` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`event_ns`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `type_workflow_map`
--

CREATE TABLE IF NOT EXISTS `type_workflow_map` (
  `document_type_id` int(11) NOT NULL DEFAULT '0',
  `workflow_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`document_type_id`),
  KEY `workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `units_lookup`
--

CREATE TABLE IF NOT EXISTS `units_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `folder_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `units_organisations_link`
--

CREATE TABLE IF NOT EXISTS `units_organisations_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL DEFAULT '0',
  `organisation_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `organisation_id` (`organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `upgrades`
--

CREATE TABLE IF NOT EXISTS `upgrades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descriptor` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_performed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `result` tinyint(1) NOT NULL DEFAULT '0',
  `parent` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `descriptor` (`descriptor`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=226 ;

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE IF NOT EXISTS `uploaded_files` (
  `tempfilename` varchar(100) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `userid` int(11) NOT NULL,
  `uploaddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` char(1) NOT NULL COMMENT 'A = Add, C = Checkin',
  `document_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`tempfilename`),
  KEY `userid` (`userid`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `quota_max` int(11) NOT NULL DEFAULT '0',
  `quota_current` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `email_notification` tinyint(1) NOT NULL DEFAULT '0',
  `sms_notification` tinyint(1) NOT NULL DEFAULT '0',
  `authentication_details_s1` varchar(255) DEFAULT NULL,
  `max_sessions` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `authentication_details_s2` varchar(255) DEFAULT NULL,
  `authentication_source_id` int(11) DEFAULT NULL,
  `authentication_details_b1` tinyint(1) DEFAULT NULL,
  `authentication_details_i2` int(11) DEFAULT NULL,
  `authentication_details_d1` datetime DEFAULT NULL,
  `authentication_details_i1` int(11) DEFAULT NULL,
  `authentication_details_d2` datetime DEFAULT NULL,
  `authentication_details_b2` tinyint(1) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `authentication_source_id` (`authentication_source_id`),
  KEY `last_login` (`last_login`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_groups_link`
--

CREATE TABLE IF NOT EXISTS `users_groups_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_history`
--

CREATE TABLE IF NOT EXISTS `user_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_namespace` varchar(255) NOT NULL,
  `comments` mediumtext,
  `session_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action_namespace` (`action_namespace`),
  KEY `datetime` (`datetime`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_history_documents`
--

CREATE TABLE IF NOT EXISTS `user_history_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `touched` datetime NOT NULL DEFAULT '1999-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_history_folders`
--

CREATE TABLE IF NOT EXISTS `user_history_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `touched` datetime NOT NULL DEFAULT '1999-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `workflows`
--

CREATE TABLE IF NOT EXISTS `workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `human_name` varchar(100) NOT NULL,
  `start_state_id` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `start_state_id` (`start_state_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_actions`
--

CREATE TABLE IF NOT EXISTS `workflow_actions` (
  `workflow_id` int(11) NOT NULL DEFAULT '0',
  `action_name` varchar(255) NOT NULL,
  PRIMARY KEY (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_documents`
--

CREATE TABLE IF NOT EXISTS `workflow_documents` (
  `document_id` int(11) NOT NULL DEFAULT '0',
  `workflow_id` int(11) NOT NULL DEFAULT '0',
  `state_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`document_id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_states`
--

CREATE TABLE IF NOT EXISTS `workflow_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `human_name` varchar(100) NOT NULL,
  `inform_descriptor_id` int(11) DEFAULT NULL,
  `manage_permissions` tinyint(1) NOT NULL DEFAULT '0',
  `manage_actions` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `name` (`name`),
  KEY `inform_descriptor_id` (`inform_descriptor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_state_actions`
--

CREATE TABLE IF NOT EXISTS `workflow_state_actions` (
  `state_id` int(11) NOT NULL DEFAULT '0',
  `action_name` varchar(255) NOT NULL,
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_state_disabled_actions`
--

CREATE TABLE IF NOT EXISTS `workflow_state_disabled_actions` (
  `state_id` int(11) NOT NULL DEFAULT '0',
  `action_name` varchar(255) NOT NULL,
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_state_permission_assignments`
--

CREATE TABLE IF NOT EXISTS `workflow_state_permission_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_state_id` int(11) NOT NULL DEFAULT '0',
  `permission_id` int(11) NOT NULL DEFAULT '0',
  `permission_descriptor_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`),
  KEY `workflow_state_id` (`workflow_state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_state_transitions`
--

CREATE TABLE IF NOT EXISTS `workflow_state_transitions` (
  `state_id` int(11) NOT NULL DEFAULT '0',
  `transition_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`state_id`,`transition_id`),
  KEY `transition_id` (`transition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_transitions`
--

CREATE TABLE IF NOT EXISTS `workflow_transitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `human_name` varchar(255) NOT NULL,
  `target_state_id` int(11) NOT NULL DEFAULT '0',
  `guard_permission_id` int(11) DEFAULT '0',
  `guard_group_id` int(11) DEFAULT '0',
  `guard_role_id` int(11) DEFAULT '0',
  `guard_condition_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_id_name` (`workflow_id`,`name`),
  KEY `target_state_id` (`target_state_id`),
  KEY `guard_condition_id` (`guard_condition_id`),
  KEY `guard_group_id` (`guard_group_id`),
  KEY `guard_role_id` (`guard_role_id`),
  KEY `name` (`name`),
  KEY `guard_permission_id` (`guard_permission_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_trigger_instances`
--

CREATE TABLE IF NOT EXISTS `workflow_trigger_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_transition_id` int(11) NOT NULL DEFAULT '0',
  `namespace` varchar(255) NOT NULL,
  `config_array` text,
  PRIMARY KEY (`id`),
  KEY `workflow_transition_id` (`workflow_transition_id`),
  KEY `namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_active_sessions`
--

CREATE TABLE IF NOT EXISTS `zseq_active_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_archive_restoration_request`
--

CREATE TABLE IF NOT EXISTS `zseq_archive_restoration_request` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_archiving_settings`
--

CREATE TABLE IF NOT EXISTS `zseq_archiving_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_archiving_type_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_archiving_type_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_authentication_sources`
--

CREATE TABLE IF NOT EXISTS `zseq_authentication_sources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_baobab_keys`
--

CREATE TABLE IF NOT EXISTS `zseq_baobab_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_baobab_user_keys`
--

CREATE TABLE IF NOT EXISTS `zseq_baobab_user_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_column_entries`
--

CREATE TABLE IF NOT EXISTS `zseq_column_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_config_settings`
--

CREATE TABLE IF NOT EXISTS `zseq_config_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_dashlet_disables`
--

CREATE TABLE IF NOT EXISTS `zseq_dashlet_disables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_data_types`
--

CREATE TABLE IF NOT EXISTS `zseq_data_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_discussion_comments`
--

CREATE TABLE IF NOT EXISTS `zseq_discussion_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_discussion_threads`
--

CREATE TABLE IF NOT EXISTS `zseq_discussion_threads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_documents`
--

CREATE TABLE IF NOT EXISTS `zseq_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_archiving_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_archiving_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_content_version`
--

CREATE TABLE IF NOT EXISTS `zseq_document_content_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_fields`
--

CREATE TABLE IF NOT EXISTS `zseq_document_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_fields_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_fields_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_link_types`
--

CREATE TABLE IF NOT EXISTS `zseq_document_link_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_metadata_version`
--

CREATE TABLE IF NOT EXISTS `zseq_document_metadata_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_role_allocations`
--

CREATE TABLE IF NOT EXISTS `zseq_document_role_allocations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_subscriptions`
--

CREATE TABLE IF NOT EXISTS `zseq_document_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_tags`
--

CREATE TABLE IF NOT EXISTS `zseq_document_tags` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_transactions`
--

CREATE TABLE IF NOT EXISTS `zseq_document_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_transaction_types_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_document_transaction_types_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_types_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_document_types_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_type_fieldsets_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_type_fieldsets_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_type_fields_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_type_fields_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_fieldsets`
--

CREATE TABLE IF NOT EXISTS `zseq_fieldsets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_field_behaviours`
--

CREATE TABLE IF NOT EXISTS `zseq_field_behaviours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_field_value_instances`
--

CREATE TABLE IF NOT EXISTS `zseq_field_value_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folders`
--

CREATE TABLE IF NOT EXISTS `zseq_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folders_users_roles_link`
--

CREATE TABLE IF NOT EXISTS `zseq_folders_users_roles_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folder_doctypes_link`
--

CREATE TABLE IF NOT EXISTS `zseq_folder_doctypes_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folder_subscriptions`
--

CREATE TABLE IF NOT EXISTS `zseq_folder_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folder_transactions`
--

CREATE TABLE IF NOT EXISTS `zseq_folder_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_groups_groups_link`
--

CREATE TABLE IF NOT EXISTS `zseq_groups_groups_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_groups_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_groups_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_help`
--

CREATE TABLE IF NOT EXISTS `zseq_help` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_help_replacement`
--

CREATE TABLE IF NOT EXISTS `zseq_help_replacement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_interceptor_instances`
--

CREATE TABLE IF NOT EXISTS `zseq_interceptor_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_links`
--

CREATE TABLE IF NOT EXISTS `zseq_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_metadata_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_metadata_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_metadata_lookup_tree`
--

CREATE TABLE IF NOT EXISTS `zseq_metadata_lookup_tree` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_mime_documents`
--

CREATE TABLE IF NOT EXISTS `zseq_mime_documents` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_mime_extractors`
--

CREATE TABLE IF NOT EXISTS `zseq_mime_extractors` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_mime_types`
--

CREATE TABLE IF NOT EXISTS `zseq_mime_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=172 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_news`
--

CREATE TABLE IF NOT EXISTS `zseq_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_notifications`
--

CREATE TABLE IF NOT EXISTS `zseq_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_organisations_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_organisations_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permissions`
--

CREATE TABLE IF NOT EXISTS `zseq_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_assignments`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_descriptors`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_descriptors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_dynamic_conditions`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_dynamic_conditions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_lookups`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_lookups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_lookup_assignments`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_lookup_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_objects`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_plugins`
--

CREATE TABLE IF NOT EXISTS `zseq_plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_plugin_helper`
--

CREATE TABLE IF NOT EXISTS `zseq_plugin_helper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_plugin_rss`
--

CREATE TABLE IF NOT EXISTS `zseq_plugin_rss` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_quicklinks`
--

CREATE TABLE IF NOT EXISTS `zseq_quicklinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_roles`
--

CREATE TABLE IF NOT EXISTS `zseq_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_role_allocations`
--

CREATE TABLE IF NOT EXISTS `zseq_role_allocations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_saved_searches`
--

CREATE TABLE IF NOT EXISTS `zseq_saved_searches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_scheduler_tasks`
--

CREATE TABLE IF NOT EXISTS `zseq_scheduler_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_search_saved`
--

CREATE TABLE IF NOT EXISTS `zseq_search_saved` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_status_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_status_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_system_settings`
--

CREATE TABLE IF NOT EXISTS `zseq_system_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_tag_words`
--

CREATE TABLE IF NOT EXISTS `zseq_tag_words` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_time_period`
--

CREATE TABLE IF NOT EXISTS `zseq_time_period` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_time_unit_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_time_unit_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_units_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_units_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_units_organisations_link`
--

CREATE TABLE IF NOT EXISTS `zseq_units_organisations_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_upgrades`
--

CREATE TABLE IF NOT EXISTS `zseq_upgrades` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=222 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_users`
--

CREATE TABLE IF NOT EXISTS `zseq_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_users_groups_link`
--

CREATE TABLE IF NOT EXISTS `zseq_users_groups_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_user_history`
--

CREATE TABLE IF NOT EXISTS `zseq_user_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_user_history_documents`
--

CREATE TABLE IF NOT EXISTS `zseq_user_history_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_user_history_folders`
--

CREATE TABLE IF NOT EXISTS `zseq_user_history_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflows`
--

CREATE TABLE IF NOT EXISTS `zseq_workflows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_states`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_states` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_state_disabled_actions`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_state_disabled_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_state_permission_assignments`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_state_permission_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_transitions`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_transitions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_trigger_instances`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_trigger_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD CONSTRAINT `active_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `archive_restoration_request`
--
ALTER TABLE `archive_restoration_request`
  ADD CONSTRAINT `archive_restoration_request_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `archive_restoration_request_ibfk_2` FOREIGN KEY (`request_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `archive_restoration_request_ibfk_3` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `archiving_settings`
--
ALTER TABLE `archiving_settings`
  ADD CONSTRAINT `archiving_settings_ibfk_1` FOREIGN KEY (`archiving_type_id`) REFERENCES `archiving_type_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `archiving_settings_ibfk_2` FOREIGN KEY (`time_period_id`) REFERENCES `time_period` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `baobab_user_keys`
--
ALTER TABLE `baobab_user_keys`
  ADD CONSTRAINT `baobab_user_keys_ibfk_4` FOREIGN KEY (`key_id`) REFERENCES `baobab_keys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `baobab_user_keys_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dashlet_disables`
--
ALTER TABLE `dashlet_disables`
  ADD CONSTRAINT `dashlet_disables_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `discussion_comments`
--
ALTER TABLE `discussion_comments`
  ADD CONSTRAINT `discussion_comments_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `discussion_threads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussion_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussion_comments_ibfk_3` FOREIGN KEY (`in_reply_to`) REFERENCES `discussion_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `discussion_threads`
--
ALTER TABLE `discussion_threads`
  ADD CONSTRAINT `discussion_threads_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussion_threads_ibfk_2` FOREIGN KEY (`first_comment_id`) REFERENCES `discussion_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussion_threads_ibfk_3` FOREIGN KEY (`last_comment_id`) REFERENCES `discussion_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussion_threads_ibfk_4` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`checked_out_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `documents_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `status_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documents_ibfk_5` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documents_ibfk_6` FOREIGN KEY (`permission_lookup_id`) REFERENCES `permission_lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documents_ibfk_7` FOREIGN KEY (`modified_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `documents_ibfk_8` FOREIGN KEY (`metadata_version_id`) REFERENCES `document_metadata_version` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_archiving_link`
--
ALTER TABLE `document_archiving_link`
  ADD CONSTRAINT `document_archiving_link_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_archiving_link_ibfk_2` FOREIGN KEY (`archiving_settings_id`) REFERENCES `archiving_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_content_version`
--
ALTER TABLE `document_content_version`
  ADD CONSTRAINT `document_content_version_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_content_version_ibfk_2` FOREIGN KEY (`mime_id`) REFERENCES `mime_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_fields`
--
ALTER TABLE `document_fields`
  ADD CONSTRAINT `document_fields_ibfk_1` FOREIGN KEY (`parent_fieldset`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_fields_link`
--
ALTER TABLE `document_fields_link`
  ADD CONSTRAINT `document_fields_link_ibfk_1` FOREIGN KEY (`document_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_fields_link_ibfk_2` FOREIGN KEY (`metadata_version_id`) REFERENCES `document_metadata_version` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_link`
--
ALTER TABLE `document_link`
  ADD CONSTRAINT `document_link_ibfk_1` FOREIGN KEY (`parent_document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_link_ibfk_2` FOREIGN KEY (`child_document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_link_ibfk_3` FOREIGN KEY (`link_type_id`) REFERENCES `document_link_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_metadata_version`
--
ALTER TABLE `document_metadata_version`
  ADD CONSTRAINT `document_metadata_version_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_metadata_version_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_metadata_version_ibfk_3` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_metadata_version_ibfk_4` FOREIGN KEY (`version_creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_metadata_version_ibfk_5` FOREIGN KEY (`content_version_id`) REFERENCES `document_content_version` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_metadata_version_ibfk_6` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_metadata_version_ibfk_7` FOREIGN KEY (`workflow_state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_role_allocations`
--
ALTER TABLE `document_role_allocations`
  ADD CONSTRAINT `document_role_allocations_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_role_allocations_ibfk_2` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_subscriptions`
--
ALTER TABLE `document_subscriptions`
  ADD CONSTRAINT `document_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_subscriptions_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_tags`
--
ALTER TABLE `document_tags`
  ADD CONSTRAINT `document_tags_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag_words` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_type_fieldsets_link`
--
ALTER TABLE `document_type_fieldsets_link`
  ADD CONSTRAINT `document_type_fieldsets_link_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_type_fieldsets_link_ibfk_2` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_type_fields_link`
--
ALTER TABLE `document_type_fields_link`
  ADD CONSTRAINT `document_type_fields_link_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_type_fields_link_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `download_files`
--
ALTER TABLE `download_files`
  ADD CONSTRAINT `download_files_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fieldsets`
--
ALTER TABLE `fieldsets`
  ADD CONSTRAINT `fieldsets_ibfk_1` FOREIGN KEY (`master_field`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `field_behaviours`
--
ALTER TABLE `field_behaviours`
  ADD CONSTRAINT `field_behaviours_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `field_behaviour_options`
--
ALTER TABLE `field_behaviour_options`
  ADD CONSTRAINT `field_behaviour_options_ibfk_1` FOREIGN KEY (`behaviour_id`) REFERENCES `field_behaviours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `field_behaviour_options_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `field_behaviour_options_ibfk_3` FOREIGN KEY (`instance_id`) REFERENCES `field_value_instances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `field_orders`
--
ALTER TABLE `field_orders`
  ADD CONSTRAINT `field_orders_ibfk_1` FOREIGN KEY (`child_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `field_orders_ibfk_2` FOREIGN KEY (`parent_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `field_orders_ibfk_3` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `field_value_instances`
--
ALTER TABLE `field_value_instances`
  ADD CONSTRAINT `field_value_instances_ibfk_1` FOREIGN KEY (`field_value_id`) REFERENCES `metadata_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `field_value_instances_ibfk_2` FOREIGN KEY (`behaviour_id`) REFERENCES `field_behaviours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `field_value_instances_ibfk_3` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folders_ibfk_2` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folders_ibfk_3` FOREIGN KEY (`permission_lookup_id`) REFERENCES `permission_lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folders_ibfk_4` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `folders_users_roles_link`
--
ALTER TABLE `folders_users_roles_link`
  ADD CONSTRAINT `folders_users_roles_link_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folders_users_roles_link_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `folder_descendants`
--
ALTER TABLE `folder_descendants`
  ADD CONSTRAINT `folder_descendants_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folder_descendants_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `folder_doctypes_link`
--
ALTER TABLE `folder_doctypes_link`
  ADD CONSTRAINT `folder_doctypes_link_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folder_doctypes_link_ibfk_2` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `folder_subscriptions`
--
ALTER TABLE `folder_subscriptions`
  ADD CONSTRAINT `folder_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folder_subscriptions_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `folder_workflow_map`
--
ALTER TABLE `folder_workflow_map`
  ADD CONSTRAINT `folder_workflow_map_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folder_workflow_map_ibfk_2` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `groups_groups_link`
--
ALTER TABLE `groups_groups_link`
  ADD CONSTRAINT `groups_groups_link_ibfk_1` FOREIGN KEY (`parent_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `groups_groups_link_ibfk_2` FOREIGN KEY (`member_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `groups_lookup`
--
ALTER TABLE `groups_lookup`
  ADD CONSTRAINT `groups_lookup_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `index_files`
--
ALTER TABLE `index_files`
  ADD CONSTRAINT `index_files_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `index_files_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `metadata_lookup`
--
ALTER TABLE `metadata_lookup`
  ADD CONSTRAINT `metadata_lookup_ibfk_1` FOREIGN KEY (`document_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `metadata_lookup_tree`
--
ALTER TABLE `metadata_lookup_tree`
  ADD CONSTRAINT `metadata_lookup_tree_ibfk_1` FOREIGN KEY (`document_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mime_document_mapping`
--
ALTER TABLE `mime_document_mapping`
  ADD CONSTRAINT `mime_document_mapping_ibfk_2` FOREIGN KEY (`mime_document_id`) REFERENCES `mime_documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mime_document_mapping_ibfk_1` FOREIGN KEY (`mime_type_id`) REFERENCES `mime_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mime_types`
--
ALTER TABLE `mime_types`
  ADD CONSTRAINT `mime_types_ibfk_1` FOREIGN KEY (`mime_document_id`) REFERENCES `mime_documents` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `mime_types_ibfk_2` FOREIGN KEY (`extractor_id`) REFERENCES `mime_extractors` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`image_mime_type_id`) REFERENCES `mime_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_assignments`
--
ALTER TABLE `permission_assignments`
  ADD CONSTRAINT `permission_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_assignments_ibfk_2` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_descriptor_groups`
--
ALTER TABLE `permission_descriptor_groups`
  ADD CONSTRAINT `permission_descriptor_groups_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_descriptor_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_descriptor_roles`
--
ALTER TABLE `permission_descriptor_roles`
  ADD CONSTRAINT `permission_descriptor_roles_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_descriptor_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_descriptor_users`
--
ALTER TABLE `permission_descriptor_users`
  ADD CONSTRAINT `permission_descriptor_users_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_descriptor_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_dynamic_assignments`
--
ALTER TABLE `permission_dynamic_assignments`
  ADD CONSTRAINT `permission_dynamic_assignments_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_dynamic_assignments_ibfk_1` FOREIGN KEY (`dynamic_condition_id`) REFERENCES `permission_dynamic_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_dynamic_conditions`
--
ALTER TABLE `permission_dynamic_conditions`
  ADD CONSTRAINT `permission_dynamic_conditions_ibfk_1` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_dynamic_conditions_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_dynamic_conditions_ibfk_3` FOREIGN KEY (`condition_id`) REFERENCES `saved_searches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_lookup_assignments`
--
ALTER TABLE `permission_lookup_assignments`
  ADD CONSTRAINT `permission_lookup_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_lookup_assignments_ibfk_2` FOREIGN KEY (`permission_lookup_id`) REFERENCES `permission_lookups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_lookup_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plugin_rss`
--
ALTER TABLE `plugin_rss`
  ADD CONSTRAINT `plugin_rss_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_allocations`
--
ALTER TABLE `role_allocations`
  ADD CONSTRAINT `role_allocations_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_allocations_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_allocations_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `saved_searches`
--
ALTER TABLE `saved_searches`
  ADD CONSTRAINT `saved_searches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `search_document_user_link`
--
ALTER TABLE `search_document_user_link`
  ADD CONSTRAINT `search_document_user_link_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `search_document_user_link_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `search_saved`
--
ALTER TABLE `search_saved`
  ADD CONSTRAINT `search_saved_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `search_saved_events`
--
ALTER TABLE `search_saved_events`
  ADD CONSTRAINT `search_saved_events_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `time_period`
--
ALTER TABLE `time_period`
  ADD CONSTRAINT `time_period_ibfk_1` FOREIGN KEY (`time_unit_id`) REFERENCES `time_unit_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `type_workflow_map`
--
ALTER TABLE `type_workflow_map`
  ADD CONSTRAINT `type_workflow_map_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `type_workflow_map_ibfk_2` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `units_lookup`
--
ALTER TABLE `units_lookup`
  ADD CONSTRAINT `units_lookup_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `units_organisations_link`
--
ALTER TABLE `units_organisations_link`
  ADD CONSTRAINT `units_organisations_link_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `units_organisations_link_ibfk_2` FOREIGN KEY (`organisation_id`) REFERENCES `organisations_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uploaded_files_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `uploaded_files_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`authentication_source_id`) REFERENCES `authentication_sources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_groups_link`
--
ALTER TABLE `users_groups_link`
  ADD CONSTRAINT `users_groups_link_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_groups_link_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_history`
--
ALTER TABLE `user_history`
  ADD CONSTRAINT `user_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflows`
--
ALTER TABLE `workflows`
  ADD CONSTRAINT `workflows_ibfk_1` FOREIGN KEY (`start_state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_documents`
--
ALTER TABLE `workflow_documents`
  ADD CONSTRAINT `workflow_documents_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_documents_ibfk_2` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_documents_ibfk_3` FOREIGN KEY (`state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_states`
--
ALTER TABLE `workflow_states`
  ADD CONSTRAINT `workflow_states_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_states_ibfk_2` FOREIGN KEY (`inform_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_state_actions`
--
ALTER TABLE `workflow_state_actions`
  ADD CONSTRAINT `workflow_state_actions_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_state_disabled_actions`
--
ALTER TABLE `workflow_state_disabled_actions`
  ADD CONSTRAINT `workflow_state_disabled_actions_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_state_permission_assignments`
--
ALTER TABLE `workflow_state_permission_assignments`
  ADD CONSTRAINT `workflow_state_permission_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_state_permission_assignments_ibfk_2` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_state_permission_assignments_ibfk_3` FOREIGN KEY (`workflow_state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_state_transitions`
--
ALTER TABLE `workflow_state_transitions`
  ADD CONSTRAINT `workflow_state_transitions_ibfk_2` FOREIGN KEY (`transition_id`) REFERENCES `workflow_transitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_state_transitions_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_transitions`
--
ALTER TABLE `workflow_transitions`
  ADD CONSTRAINT `workflow_transitions_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_transitions_ibfk_2` FOREIGN KEY (`target_state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_transitions_ibfk_3` FOREIGN KEY (`guard_permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_transitions_ibfk_4` FOREIGN KEY (`guard_condition_id`) REFERENCES `saved_searches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_transitions_ibfk_5` FOREIGN KEY (`guard_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_transitions_ibfk_6` FOREIGN KEY (`guard_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflow_trigger_instances`
--
ALTER TABLE `workflow_trigger_instances`
  ADD CONSTRAINT `workflow_trigger_instances_ibfk_1` FOREIGN KEY (`workflow_transition_id`) REFERENCES `workflow_transitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
