--
-- $Id$
--    
-- The contents of this file are subject to the KnowledgeTree Public
-- License Version 1.1.2 ("License"); You may not use this file except in
-- compliance with the License. You may obtain a copy of the License at
-- http://www.knowledgetree.com/KPL
-- 
-- Software distributed under the License is distributed on an "AS IS"
-- basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
-- See the License for the specific language governing rights and
-- limitations under the License.
--
-- All copies of the Covered Code must include on each user interface screen:
--    (i) the "Powered by KnowledgeTree" logo and
--    (ii) the KnowledgeTree copyright notice
-- in the same form as they appear in the distribution.  See the License for
-- requirements.
-- 
-- The Original Code is: KnowledgeTree Open Source
-- 
-- The Initial Developer of the Original Code is The Jam Warehouse Software
-- (Pty) Ltd, trading as KnowledgeTree.
-- Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
-- (C) 2007 The Jam Warehouse Software (Pty) Ltd;
-- All Rights Reserved.
-- Contributor( s): ______________________________________
--
-- MySQL dump 10.11
--
-- Host: localhost    Database: dms_clean
-- ------------------------------------------------------
-- Server version	5.0.37

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `active_sessions`
--

CREATE TABLE `active_sessions` (
  `id` int(11) NOT NULL default '0',
  `user_id` int(11) default NULL,
  `session_id` char(255) default NULL,
  `lastused` datetime default NULL,
  `ip` char(30) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `session_id_idx` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `archive_restoration_request`
--

CREATE TABLE `archive_restoration_request` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `request_user_id` int(11) NOT NULL default '0',
  `admin_user_id` int(11) NOT NULL default '0',
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `archiving_settings`
--

CREATE TABLE `archiving_settings` (
  `id` int(11) NOT NULL default '0',
  `archiving_type_id` int(11) NOT NULL default '0',
  `expiration_date` date default NULL,
  `document_transaction_id` int(11) default NULL,
  `time_period_id` int(11) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `archiving_type_lookup`
--

CREATE TABLE `archiving_type_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `authentication_sources`
--

CREATE TABLE `authentication_sources` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `namespace` varchar(255) NOT NULL default '',
  `authentication_provider` varchar(255) NOT NULL default '',
  `config` mediumtext NOT NULL,
  `is_user_source` tinyint(1) NOT NULL default '0',
  `is_group_source` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `column_entries`
--

CREATE TABLE `column_entries` (
  `id` int(11) NOT NULL default '0',
  `column_namespace` varchar(255) NOT NULL default '',
  `view_namespace` varchar(255) NOT NULL default '',
  `config_array` text NOT NULL,
  `position` int(11) NOT NULL default '0',
  `required` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `view_namespace` (`view_namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `comment_searchable_text`
--

CREATE TABLE `comment_searchable_text` (
  `comment_id` int(11) NOT NULL default '0',
  `body` mediumtext,
  `document_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`comment_id`),
  FULLTEXT KEY `comment_search_text` (`body`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `dashlet_disables`
--

CREATE TABLE `dashlet_disables` (
  `id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `dashlet_namespace` varchar(255) NOT NULL default '',
  UNIQUE KEY `id` (`id`),
  KEY `user_id` (`user_id`),
  KEY `dashlet_namespace` (`dashlet_namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `data_types`
--

CREATE TABLE `data_types` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `discussion_comments`
--

CREATE TABLE `discussion_comments` (
  `id` int(11) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  `in_reply_to` int(11) default NULL,
  `user_id` int(11) NOT NULL default '0',
  `subject` mediumtext,
  `body` mediumtext,
  `date` datetime default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `discussion_threads`
--

CREATE TABLE `discussion_threads` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `first_comment_id` int(11) NOT NULL default '0',
  `last_comment_id` int(11) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `replies` int(11) NOT NULL default '0',
  `creator_id` int(11) NOT NULL default '0',
  `close_reason` mediumtext NOT NULL,
  `close_metadata_version` int(11) NOT NULL default '0',
  `state` int(1) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_archiving_link`
--

CREATE TABLE `document_archiving_link` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `archiving_settings_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_content_version`
--

CREATE TABLE `document_content_version` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `filename` mediumtext NOT NULL,
  `size` bigint(20) NOT NULL default '0',
  `mime_id` int(11) NOT NULL default '0',
  `major_version` int(11) NOT NULL default '0',
  `minor_version` int(11) NOT NULL default '0',
  `storage_path` varchar(250) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `storage_path` (`storage_path`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_fields`
--

CREATE TABLE `document_fields` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `data_type` varchar(100) NOT NULL default '',
  `is_generic` tinyint(1) default NULL,
  `has_lookup` tinyint(1) default NULL,
  `has_lookuptree` tinyint(1) default NULL,
  `parent_fieldset` int(11) default NULL,
  `is_mandatory` tinyint(4) NOT NULL default '0',
  `description` mediumtext NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `parent_fieldset` (`parent_fieldset`),
  CONSTRAINT `document_fields_ibfk_1` FOREIGN KEY (`parent_fieldset`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_fields_link`
--

CREATE TABLE `document_fields_link` (
  `id` int(11) NOT NULL default '0',
  `document_field_id` int(11) NOT NULL default '0',
  `value` char(255) NOT NULL default '',
  `metadata_version_id` int(11) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `document_field_id` (`document_field_id`),
  KEY `metadata_version_id` (`metadata_version_id`),
  CONSTRAINT `document_fields_link_ibfk_2` FOREIGN KEY (`document_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_incomplete`
--

CREATE TABLE `document_incomplete` (
  `id` int(10) unsigned NOT NULL default '0',
  `contents` tinyint(1) unsigned NOT NULL default '0',
  `metadata` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_link`
--

CREATE TABLE `document_link` (
  `id` int(11) NOT NULL default '0',
  `parent_document_id` int(11) NOT NULL default '0',
  `child_document_id` int(11) NOT NULL default '0',
  `link_type_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_link_types`
--

CREATE TABLE `document_link_types` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) NOT NULL default '',
  `reverse_name` char(100) NOT NULL default '',
  `description` char(255) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_metadata_version`
--

CREATE TABLE `document_metadata_version` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `content_version_id` int(11) NOT NULL default '0',
  `document_type_id` int(11) NOT NULL default '0',
  `name` mediumtext NOT NULL,
  `description` varchar(200) NOT NULL default '',
  `status_id` int(11) default NULL,
  `metadata_version` int(11) NOT NULL default '0',
  `version_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `version_creator_id` int(11) NOT NULL default '0',
  `workflow_id` int(11) default NULL,
  `workflow_state_id` int(11) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `fk_document_type_id` (`document_type_id`),
  KEY `fk_status_id` (`status_id`),
  KEY `document_id` (`document_id`),
  KEY `version_created` (`version_created`),
  KEY `version_creator_id` (`version_creator_id`),
  KEY `content_version_id` (`content_version_id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `workflow_state_id` (`workflow_state_id`),
  CONSTRAINT `document_metadata_version_ibfk_4` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_metadata_version_ibfk_5` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`),
  CONSTRAINT `document_metadata_version_ibfk_6` FOREIGN KEY (`status_id`) REFERENCES `status_lookup` (`id`),
  CONSTRAINT `document_metadata_version_ibfk_7` FOREIGN KEY (`version_creator_id`) REFERENCES `users` (`id`),
  CONSTRAINT `document_metadata_version_ibfk_8` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`),
  CONSTRAINT `document_metadata_version_ibfk_9` FOREIGN KEY (`workflow_state_id`) REFERENCES `workflow_states` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_role_allocations`
--

CREATE TABLE `document_role_allocations` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `role_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_searchable_text`
--

CREATE TABLE `document_searchable_text` (
  `document_id` int(11) default NULL,
  `document_text` longtext,
  KEY `document_text_document_id_indx` (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_subscriptions`
--

CREATE TABLE `document_subscriptions` (
  `id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `is_alerted` tinyint(1) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_tags`
--

CREATE TABLE `document_tags` (
  `document_id` int(10) NOT NULL,
  `tag_id` int(10) NOT NULL,
  PRIMARY KEY  (`document_id`,`tag_id`),
  KEY `fk_document_tags_tag_id` (`tag_id`),
  CONSTRAINT `fk_document_tags_document_id` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_tags_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tag_words` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_text`
--

CREATE TABLE `document_text` (
  `document_id` int(11) default NULL,
  `document_text` longtext,
  KEY `document_text_document_id_indx` (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_transaction_text`
--

CREATE TABLE `document_transaction_text` (
  `document_id` int(11) default NULL,
  `document_text` mediumtext,
  KEY `document_text_document_id_indx` (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_transaction_types_lookup`
--

CREATE TABLE `document_transaction_types_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `namespace` varchar(250) NOT NULL default '',
  UNIQUE KEY `id` (`id`),
  KEY `namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_transactions`
--

CREATE TABLE `document_transactions` (
  `id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `version` char(50) default NULL,
  `user_id` int(11) NOT NULL default '0',
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` char(30) default NULL,
  `filename` char(255) NOT NULL default '',
  `comment` char(255) NOT NULL default '',
  `transaction_namespace` char(255) NOT NULL default 'ktcore.transactions.event',
  `session_id` int(11) default NULL,
  `admin_mode` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `fk_document_id` (`document_id`),
  KEY `fk_user_id` (`user_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_type_fields_link`
--

CREATE TABLE `document_type_fields_link` (
  `id` int(11) NOT NULL default '0',
  `document_type_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `is_mandatory` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_type_fieldsets_link`
--

CREATE TABLE `document_type_fieldsets_link` (
  `id` int(11) NOT NULL default '0',
  `document_type_id` int(11) NOT NULL default '0',
  `fieldset_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `document_type_id` (`document_type_id`),
  KEY `fieldset_id` (`fieldset_id`),
  CONSTRAINT `document_type_fieldsets_link_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types_lookup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_type_fieldsets_link_ibfk_2` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `document_types_lookup`
--

CREATE TABLE `document_types_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) default NULL,
  `disabled` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL default '0',
  `creator_id` int(11) NOT NULL default '0',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `folder_id` int(11) default NULL,
  `is_checked_out` tinyint(1) NOT NULL default '0',
  `parent_folder_ids` mediumtext,
  `full_path` mediumtext,
  `checked_out_user_id` int(11) default NULL,
  `status_id` int(11) default NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `permission_object_id` int(11) default NULL,
  `permission_lookup_id` int(11) default NULL,
  `metadata_version` int(11) NOT NULL default '0',
  `modified_user_id` int(11) NOT NULL default '0',
  `metadata_version_id` int(11) default NULL,
  `owner_id` int(11) NOT NULL default '0',
  `immutable` tinyint(1) NOT NULL default '0',
  `restore_folder_id` int(11) default NULL,
  `restore_folder_path` text,
  `checkedout` datetime default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `fk_creator_id` (`creator_id`),
  KEY `fk_folder_id` (`folder_id`),
  KEY `fk_checked_out_user_id` (`checked_out_user_id`),
  KEY `fk_status_id` (`status_id`),
  KEY `created` (`created`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `permission_lookup_id` (`permission_lookup_id`),
  KEY `modified_user_id` (`modified_user_id`),
  KEY `metadata_version_id` (`metadata_version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `download_files`
--

CREATE TABLE `download_files` (
  `document_id` int(10) unsigned NOT NULL,
  `session` varchar(100) NOT NULL,
  `download_date` timestamp NULL default CURRENT_TIMESTAMP,
  `downloaded` int(10) unsigned NOT NULL default '0',
  `filesize` int(10) unsigned NOT NULL,
  `content_version` int(10) unsigned NOT NULL,
  `hash` varchar(100) NOT NULL,
  PRIMARY KEY  (`document_id`,`session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `field_behaviour_options`
--

CREATE TABLE `field_behaviour_options` (
  `behaviour_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `instance_id` int(11) NOT NULL default '0',
  KEY `behaviour_id` (`behaviour_id`),
  KEY `field_id` (`field_id`),
  KEY `instance_id` (`instance_id`),
  CONSTRAINT `field_behaviour_options_ibfk_1` FOREIGN KEY (`behaviour_id`) REFERENCES `field_behaviours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_behaviour_options_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_behaviour_options_ibfk_3` FOREIGN KEY (`instance_id`) REFERENCES `field_value_instances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `field_behaviours`
--

CREATE TABLE `field_behaviours` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `field_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`),
  KEY `name` (`name`),
  CONSTRAINT `field_behaviours_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `field_orders`
--

CREATE TABLE `field_orders` (
  `parent_field_id` int(11) NOT NULL default '0',
  `child_field_id` int(11) NOT NULL default '0',
  `fieldset_id` int(11) NOT NULL default '0',
  UNIQUE KEY `child_field` (`child_field_id`),
  KEY `parent_field` (`parent_field_id`),
  KEY `fieldset_id` (`fieldset_id`),
  CONSTRAINT `field_orders_ibfk_1` FOREIGN KEY (`parent_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_orders_ibfk_2` FOREIGN KEY (`child_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_orders_ibfk_3` FOREIGN KEY (`fieldset_id`) REFERENCES `fieldsets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `field_value_instances`
--

CREATE TABLE `field_value_instances` (
  `id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `field_value_id` int(11) NOT NULL default '0',
  `behaviour_id` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`),
  KEY `field_value_id` (`field_value_id`),
  KEY `behaviour_id` (`behaviour_id`),
  CONSTRAINT `field_value_instances_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_value_instances_ibfk_2` FOREIGN KEY (`field_value_id`) REFERENCES `metadata_lookup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_value_instances_ibfk_3` FOREIGN KEY (`behaviour_id`) REFERENCES `field_behaviours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `fieldsets`
--

CREATE TABLE `fieldsets` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `namespace` varchar(255) NOT NULL default '',
  `mandatory` tinyint(4) NOT NULL default '0',
  `is_conditional` tinyint(1) NOT NULL default '0',
  `master_field` int(11) default NULL,
  `is_generic` tinyint(1) NOT NULL default '0',
  `is_complex` tinyint(1) NOT NULL default '0',
  `is_complete` tinyint(1) NOT NULL default '1',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `description` mediumtext NOT NULL,
  `disabled` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `is_generic` (`is_generic`),
  KEY `is_complete` (`is_complete`),
  KEY `is_system` (`is_system`),
  KEY `master_field` (`master_field`),
  CONSTRAINT `fieldsets_ibfk_1` FOREIGN KEY (`master_field`) REFERENCES `document_fields` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `folder_doctypes_link`
--

CREATE TABLE `folder_doctypes_link` (
  `id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `document_type_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `fk_folder_id` (`folder_id`),
  KEY `fk_document_type_id` (`document_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `folder_searchable_text`
--

CREATE TABLE `folder_searchable_text` (
  `folder_id` int(11) NOT NULL default '0',
  `folder_text` mediumtext,
  PRIMARY KEY  (`folder_id`),
  KEY `folder_searchable_text_folder_indx` (`folder_id`),
  FULLTEXT KEY `folder_text` (`folder_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `folder_subscriptions`
--

CREATE TABLE `folder_subscriptions` (
  `id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `is_alerted` tinyint(1) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `folder_transactions`
--

CREATE TABLE `folder_transactions` (
  `id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` char(30) default NULL,
  `comment` char(255) NOT NULL default '',
  `transaction_namespace` char(255) NOT NULL default 'ktcore.transactions.event',
  `session_id` int(11) default NULL,
  `admin_mode` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `folder_transactions_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folder_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `folder_workflow_map`
--

CREATE TABLE `folder_workflow_map` (
  `folder_id` int(11) NOT NULL default '0',
  `workflow_id` int(11) default NULL,
  PRIMARY KEY  (`folder_id`),
  UNIQUE KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `parent_id` int(11) default NULL,
  `creator_id` int(11) default NULL,
  `is_public` tinyint(1) NOT NULL default '0',
  `parent_folder_ids` mediumtext,
  `full_path` mediumtext,
  `permission_object_id` int(11) default NULL,
  `permission_lookup_id` int(11) default NULL,
  `restrict_document_types` tinyint(1) NOT NULL default '0',
  `owner_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `fk_parent_id` (`parent_id`),
  KEY `fk_creator_id` (`creator_id`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `permission_lookup_id` (`permission_lookup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `folders_users_roles_link`
--

CREATE TABLE `folders_users_roles_link` (
  `id` int(11) NOT NULL default '0',
  `group_folder_approval_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `document_id` int(11) NOT NULL default '0',
  `datetime` datetime default NULL,
  `done` tinyint(1) default NULL,
  `active` tinyint(1) default NULL,
  `dependant_documents_created` tinyint(1) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `groups_groups_link`
--

CREATE TABLE `groups_groups_link` (
  `id` int(11) NOT NULL default '0',
  `parent_group_id` int(11) NOT NULL default '0',
  `member_group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `groups_groups_link_ibfk_1` (`parent_group_id`),
  KEY `groups_groups_link_ibfk_2` (`member_group_id`),
  CONSTRAINT `groups_groups_link_ibfk_1` FOREIGN KEY (`parent_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groups_groups_link_ibfk_2` FOREIGN KEY (`member_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `groups_lookup`
--

CREATE TABLE `groups_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `is_sys_admin` tinyint(1) NOT NULL default '0',
  `is_unit_admin` tinyint(1) NOT NULL default '0',
  `unit_id` int(11) default NULL,
  `authentication_details_s2` varchar(255) default NULL,
  `authentication_details_s1` varchar(255) default NULL,
  `authentication_source_id` int(11) default NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `unit_id` (`unit_id`),
  KEY `authentication_details_s1` (`authentication_details_s1`),
  KEY `authentication_source_id` (`authentication_source_id`),
  CONSTRAINT `groups_lookup_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units_lookup` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `help`
--

CREATE TABLE `help` (
  `id` int(11) NOT NULL default '0',
  `fSection` varchar(100) NOT NULL default '',
  `help_info` mediumtext NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `help_replacement`
--

CREATE TABLE `help_replacement` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `index_files`
--

CREATE TABLE `index_files` (
  `document_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `indexdate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `processdate` datetime default NULL,
  `what` char(1) default NULL,
  PRIMARY KEY  (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `interceptor_instances`
--

CREATE TABLE `interceptor_instances` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `interceptor_namespace` varchar(255) NOT NULL,
  `config` text,
  PRIMARY KEY  (`id`),
  KEY `interceptor_namespace` (`interceptor_namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) NOT NULL default '',
  `url` char(100) NOT NULL default '',
  `rank` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `metadata_lookup`
--

CREATE TABLE `metadata_lookup` (
  `id` int(11) NOT NULL default '0',
  `document_field_id` int(11) NOT NULL default '0',
  `name` char(255) default NULL,
  `treeorg_parent` int(11) default NULL,
  `disabled` tinyint(3) unsigned NOT NULL default '0',
  `is_stuck` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `disabled` (`disabled`),
  KEY `is_stuck` (`is_stuck`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `metadata_lookup_tree`
--

CREATE TABLE `metadata_lookup_tree` (
  `id` int(11) NOT NULL default '0',
  `document_field_id` int(11) NOT NULL default '0',
  `name` char(255) default NULL,
  `metadata_lookup_tree_parent` int(11) default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `metadata_lookup_tree_parent` (`metadata_lookup_tree_parent`),
  KEY `document_field_id` (`document_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `mime_documents`
--

CREATE TABLE `mime_documents` (
  `id` int(11) NOT NULL,
  `mime_doc` varchar(100) default NULL,
  `icon_path` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `mime_types`
--

CREATE TABLE `mime_types` (
  `id` int(11) NOT NULL default '0',
  `filetypes` char(100) NOT NULL default '',
  `mimetypes` char(100) NOT NULL default '',
  `icon_path` char(255) default NULL,
  `friendly_name` char(255) default '',
  `extractor` varchar(100) default NULL,
  `mime_document_id` int(11) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL default '0',
  `synopsis` varchar(255) NOT NULL default '',
  `body` mediumtext,
  `rank` int(11) default NULL,
  `image` mediumtext,
  `image_size` int(11) default NULL,
  `image_mime_type_id` int(11) default NULL,
  `active` tinyint(1) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `label` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `data_int_1` int(11) default NULL,
  `data_int_2` int(11) default NULL,
  `data_str_1` varchar(255) default NULL,
  `data_str_2` varchar(255) default NULL,
  `data_text_1` text,
  UNIQUE KEY `id` (`id`),
  KEY `type` (`type`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `organisations_lookup`
--

CREATE TABLE `organisations_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) NOT NULL default '',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_assignments`
--

CREATE TABLE `permission_assignments` (
  `id` int(11) NOT NULL default '0',
  `permission_id` int(11) NOT NULL default '0',
  `permission_object_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `permission_and_object` (`permission_id`,`permission_object_id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`),
  CONSTRAINT `permission_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_assignments_ibfk_2` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_descriptor_groups`
--

CREATE TABLE `permission_descriptor_groups` (
  `descriptor_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  UNIQUE KEY `descriptor_id` (`descriptor_id`,`group_id`),
  KEY `descriptor_id_2` (`descriptor_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `permission_descriptor_groups_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_descriptor_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_descriptor_roles`
--

CREATE TABLE `permission_descriptor_roles` (
  `descriptor_id` int(11) NOT NULL default '0',
  `role_id` int(11) NOT NULL default '0',
  UNIQUE KEY `descriptor_id` (`descriptor_id`,`role_id`),
  KEY `descriptor_id_2` (`descriptor_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `permission_descriptor_roles_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_descriptor_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_descriptor_users`
--

CREATE TABLE `permission_descriptor_users` (
  `descriptor_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  UNIQUE KEY `descriptor_id` (`descriptor_id`,`user_id`),
  KEY `descriptor_id_2` (`descriptor_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `permission_descriptor_users_ibfk_1` FOREIGN KEY (`descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_descriptor_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_descriptors`
--

CREATE TABLE `permission_descriptors` (
  `id` int(11) NOT NULL default '0',
  `descriptor` varchar(32) NOT NULL default '',
  `descriptor_text` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `descriptor_2` (`descriptor`),
  KEY `descriptor` (`descriptor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_dynamic_assignments`
--

CREATE TABLE `permission_dynamic_assignments` (
  `dynamic_condition_id` int(11) NOT NULL default '0',
  `permission_id` int(11) NOT NULL default '0',
  KEY `dynamic_conditiond_id` (`dynamic_condition_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `permission_dynamic_assignments_ibfk_2` FOREIGN KEY (`dynamic_condition_id`) REFERENCES `permission_dynamic_conditions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_dynamic_assignments_ibfk_3` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_dynamic_conditions`
--

CREATE TABLE `permission_dynamic_conditions` (
  `id` int(11) NOT NULL default '0',
  `permission_object_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `condition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `permission_object_id` (`permission_object_id`),
  KEY `group_id` (`group_id`),
  KEY `condition_id` (`condition_id`),
  CONSTRAINT `permission_dynamic_conditions_ibfk_1` FOREIGN KEY (`permission_object_id`) REFERENCES `permission_objects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_dynamic_conditions_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_dynamic_conditions_ibfk_3` FOREIGN KEY (`condition_id`) REFERENCES `saved_searches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_lookup_assignments`
--

CREATE TABLE `permission_lookup_assignments` (
  `id` int(11) NOT NULL default '0',
  `permission_id` int(11) NOT NULL default '0',
  `permission_lookup_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `permission_and_lookup` (`permission_id`,`permission_lookup_id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_lookup_id` (`permission_lookup_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`),
  CONSTRAINT `permission_lookup_assignments_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_lookup_assignments_ibfk_2` FOREIGN KEY (`permission_lookup_id`) REFERENCES `permission_lookups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_lookup_assignments_ibfk_3` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_lookups`
--

CREATE TABLE `permission_lookups` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permission_objects`
--

CREATE TABLE `permission_objects` (
  `id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `built_in` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `plugin_rss`
--

CREATE TABLE `plugin_rss` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  `title` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `plugins`
--

CREATE TABLE `plugins` (
  `id` int(11) NOT NULL default '0',
  `namespace` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `version` int(11) NOT NULL default '0',
  `disabled` tinyint(1) NOT NULL default '0',
  `data` mediumtext,
  `unavailable` tinyint(1) NOT NULL default '0',
  `friendly_name` varchar(255) default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `role_allocations`
--

CREATE TABLE `role_allocations` (
  `id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `role_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `saved_searches`
--

CREATE TABLE `saved_searches` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `namespace` varchar(250) NOT NULL default '',
  `is_condition` tinyint(1) NOT NULL default '0',
  `is_complete` tinyint(1) NOT NULL default '0',
  `user_id` int(10) default NULL,
  `search` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `namespace` (`namespace`),
  KEY `is_condition` (`is_condition`),
  KEY `is_complete` (`is_complete`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `saved_searches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `scheduler_tasks`
--

CREATE TABLE `scheduler_tasks` (
  `id` int(11) NOT NULL default '0',
  `task` varchar(50) default NULL,
  `script_url` varchar(255) default NULL,
  `script_params` varchar(255) default NULL,
  `on_completion` varchar(255) default NULL,
  `is_background` tinyint(4) NOT NULL default '0',
  `is_complete` tinyint(4) NOT NULL default '0',
  `frequency` varchar(25) default NULL,
  `run_time` int(11) default NULL,
  `previous_run_time` int(11) default NULL,
  `run_duration` float default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `search_document_user_link`
--

CREATE TABLE `search_document_user_link` (
  `document_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  KEY `fk_user_id` (`user_id`),
  KEY `fk_document_ids` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `search_ranking`
--

CREATE TABLE `search_ranking` (
  `groupname` varchar(100) NOT NULL,
  `itemname` varchar(100) NOT NULL,
  `ranking` float default '0',
  `type` enum('T','M','S') default 'T' COMMENT 'T=Table, M=Metadata, S=Searchable',
  PRIMARY KEY  (`groupname`,`itemname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `search_saved`
--

CREATE TABLE `search_saved` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `expression` tinytext NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('S','C','W','B') NOT NULL default 'S' COMMENT 'S=saved search, C=permission, w=workflow, B=subscription',
  `shared` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `search_saved_events`
--

CREATE TABLE `search_saved_events` (
  `document_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `status_lookup`
--

CREATE TABLE `status_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tag_words`
--

CREATE TABLE `tag_words` (
  `id` int(10) NOT NULL,
  `tag` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `time_period`
--

CREATE TABLE `time_period` (
  `id` int(11) NOT NULL default '0',
  `time_unit_id` int(11) default NULL,
  `units` int(11) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `time_unit_lookup`
--

CREATE TABLE `time_unit_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `trigger_selection`
--

CREATE TABLE `trigger_selection` (
  `event_ns` varchar(255) NOT NULL default '',
  `selection_ns` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`event_ns`),
  UNIQUE KEY `event_ns` (`event_ns`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `type_workflow_map`
--

CREATE TABLE `type_workflow_map` (
  `document_type_id` int(11) NOT NULL default '0',
  `workflow_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`document_type_id`),
  UNIQUE KEY `document_type_id` (`document_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `units_lookup`
--

CREATE TABLE `units_lookup` (
  `id` int(11) NOT NULL default '0',
  `name` char(100) NOT NULL default '',
  `folder_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `units_organisations_link`
--

CREATE TABLE `units_organisations_link` (
  `id` int(11) NOT NULL default '0',
  `unit_id` int(11) NOT NULL default '0',
  `organisation_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `fk_unit_id` (`unit_id`),
  KEY `fk_organisation_id` (`organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `upgrades`
--

CREATE TABLE `upgrades` (
  `id` int(10) unsigned NOT NULL default '0',
  `descriptor` char(100) NOT NULL default '',
  `description` char(255) NOT NULL default '',
  `date_performed` datetime NOT NULL default '0000-00-00 00:00:00',
  `result` tinyint(4) NOT NULL default '0',
  `parent` char(40) default NULL,
  PRIMARY KEY  (`id`),
  KEY `descriptor` (`descriptor`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `tempfilename` varchar(100) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `userid` int(11) NOT NULL,
  `uploaddate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `action` char(1) NOT NULL COMMENT 'A = Add, C = Checkin',
  `document_id` int(11) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `user_history`
--

CREATE TABLE `user_history` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_namespace` varchar(255) NOT NULL,
  `comments` mediumtext,
  `session_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `action_namespace` (`action_namespace`),
  KEY `datetime` (`datetime`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `user_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL default '0',
  `username` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `quota_max` int(11) NOT NULL default '0',
  `quota_current` int(11) NOT NULL default '0',
  `email` varchar(255) default NULL,
  `mobile` varchar(255) default NULL,
  `email_notification` tinyint(1) NOT NULL default '0',
  `sms_notification` tinyint(1) NOT NULL default '0',
  `authentication_details_s1` varchar(255) default NULL,
  `max_sessions` int(11) default NULL,
  `language_id` int(11) default NULL,
  `authentication_details_s2` varchar(255) default NULL,
  `authentication_source_id` int(11) default NULL,
  `authentication_details_b1` tinyint(1) default NULL,
  `authentication_details_i2` int(11) default NULL,
  `authentication_details_d1` datetime default NULL,
  `authentication_details_i1` int(11) default NULL,
  `authentication_details_d2` datetime default NULL,
  `authentication_details_b2` tinyint(1) default NULL,
  `last_login` datetime default NULL,
  `disabled` tinyint(1) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `authentication_source` (`authentication_source_id`),
  KEY `authentication_details_b1` (`authentication_details_b1`),
  KEY `authentication_details_b2` (`authentication_details_b2`),
  KEY `last_login` (`last_login`),
  KEY `disabled` (`disabled`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`authentication_source_id`) REFERENCES `authentication_sources` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `users_groups_link`
--

CREATE TABLE `users_groups_link` (
  `id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `fk_user_id` (`user_id`),
  KEY `fk_group_id` (`group_id`),
  CONSTRAINT `users_groups_link_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_groups_link_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_actions`
--

CREATE TABLE `workflow_actions` (
  `workflow_id` int(11) NOT NULL default '0',
  `action_name` char(255) NOT NULL default '',
  KEY `workflow_id` (`workflow_id`),
  KEY `action_name` (`action_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_documents`
--

CREATE TABLE `workflow_documents` (
  `document_id` int(11) NOT NULL default '0',
  `workflow_id` int(11) NOT NULL default '0',
  `state_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`document_id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_state_actions`
--

CREATE TABLE `workflow_state_actions` (
  `state_id` int(11) NOT NULL default '0',
  `action_name` char(255) NOT NULL default '0',
  KEY `state_id` (`state_id`),
  KEY `action_name` (`action_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_state_disabled_actions`
--

CREATE TABLE `workflow_state_disabled_actions` (
  `state_id` int(11) NOT NULL default '0',
  `action_name` char(255) NOT NULL default '0',
  KEY `state_id` (`state_id`),
  KEY `action_name` (`action_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_state_permission_assignments`
--

CREATE TABLE `workflow_state_permission_assignments` (
  `id` int(11) NOT NULL default '0',
  `workflow_state_id` int(11) NOT NULL default '0',
  `permission_id` int(11) NOT NULL default '0',
  `permission_descriptor_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `permission_id` (`permission_id`),
  KEY `permission_descriptor_id` (`permission_descriptor_id`),
  KEY `workflow_state_id` (`workflow_state_id`),
  CONSTRAINT `workflow_state_permission_assignments_ibfk_7` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workflow_state_permission_assignments_ibfk_8` FOREIGN KEY (`permission_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_state_transitions`
--

CREATE TABLE `workflow_state_transitions` (
  `state_id` int(11) NOT NULL default '0',
  `transition_id` int(11) NOT NULL default '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_states`
--

CREATE TABLE `workflow_states` (
  `id` int(11) NOT NULL default '0',
  `workflow_id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `inform_descriptor_id` int(11) default NULL,
  `manage_permissions` int(1) unsigned NOT NULL default '0',
  `manage_actions` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `name` (`name`),
  KEY `inform_descriptor_id` (`inform_descriptor_id`),
  CONSTRAINT `workflow_states_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`),
  CONSTRAINT `workflow_states_ibfk_2` FOREIGN KEY (`inform_descriptor_id`) REFERENCES `permission_descriptors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_transitions`
--

CREATE TABLE `workflow_transitions` (
  `id` int(11) NOT NULL default '0',
  `workflow_id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `target_state_id` int(11) NOT NULL default '0',
  `guard_permission_id` int(11) default '0',
  `guard_group_id` int(11) default '0',
  `guard_role_id` int(11) default '0',
  `guard_condition_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `workflow_id_2` (`workflow_id`,`name`),
  KEY `workflow_id` (`workflow_id`),
  KEY `name` (`name`),
  KEY `target_state_id` (`target_state_id`),
  KEY `guard_permission_id` (`guard_permission_id`),
  KEY `guard_condition` (`guard_condition_id`),
  KEY `guard_group_id` (`guard_group_id`),
  KEY `guard_role_id` (`guard_role_id`),
  CONSTRAINT `workflow_transitions_ibfk_45` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workflow_transitions_ibfk_46` FOREIGN KEY (`target_state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workflow_transitions_ibfk_47` FOREIGN KEY (`guard_permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `workflow_transitions_ibfk_48` FOREIGN KEY (`guard_group_id`) REFERENCES `groups_lookup` (`id`) ON DELETE SET NULL,
  CONSTRAINT `workflow_transitions_ibfk_49` FOREIGN KEY (`guard_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `workflow_transitions_ibfk_50` FOREIGN KEY (`guard_condition_id`) REFERENCES `saved_searches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `workflow_trigger_instances`
--

CREATE TABLE `workflow_trigger_instances` (
  `id` int(10) unsigned NOT NULL default '0',
  `workflow_transition_id` int(11) NOT NULL default '0',
  `namespace` char(255) NOT NULL default '',
  `config_array` text,
  PRIMARY KEY  (`id`),
  KEY `workflow_transition_id` (`workflow_transition_id`),
  KEY `namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `workflows`
--

CREATE TABLE `workflows` (
  `id` int(11) NOT NULL default '0',
  `name` char(250) NOT NULL default '',
  `human_name` char(100) NOT NULL default '',
  `start_state_id` int(11) default NULL,
  `enabled` int(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `start_state_id` (`start_state_id`),
  CONSTRAINT `workflows_ibfk_1` FOREIGN KEY (`start_state_id`) REFERENCES `workflow_states` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `zseq_active_sessions`
--

CREATE TABLE `zseq_active_sessions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_archive_restoration_request`
--

CREATE TABLE `zseq_archive_restoration_request` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_archiving_settings`
--

CREATE TABLE `zseq_archiving_settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_archiving_type_lookup`
--

CREATE TABLE `zseq_archiving_type_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_authentication_sources`
--

CREATE TABLE `zseq_authentication_sources` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_browse_criteria`
--

CREATE TABLE `zseq_browse_criteria` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_column_entries`
--

CREATE TABLE `zseq_column_entries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_dashlet_disables`
--

CREATE TABLE `zseq_dashlet_disables` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_data_types`
--

CREATE TABLE `zseq_data_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_dependant_document_instance`
--

CREATE TABLE `zseq_dependant_document_instance` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_dependant_document_template`
--

CREATE TABLE `zseq_dependant_document_template` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_discussion_comments`
--

CREATE TABLE `zseq_discussion_comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_discussion_threads`
--

CREATE TABLE `zseq_discussion_threads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_archiving_link`
--

CREATE TABLE `zseq_document_archiving_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_content_version`
--

CREATE TABLE `zseq_document_content_version` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_fields`
--

CREATE TABLE `zseq_document_fields` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_fields_link`
--

CREATE TABLE `zseq_document_fields_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_link`
--

CREATE TABLE `zseq_document_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_link_types`
--

CREATE TABLE `zseq_document_link_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_metadata_version`
--

CREATE TABLE `zseq_document_metadata_version` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_role_allocations`
--

CREATE TABLE `zseq_document_role_allocations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_subscriptions`
--

CREATE TABLE `zseq_document_subscriptions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_tags`
--

CREATE TABLE `zseq_document_tags` (
  `id` int(10) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `zseq_document_transaction_types_lookup`
--

CREATE TABLE `zseq_document_transaction_types_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_transactions`
--

CREATE TABLE `zseq_document_transactions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_type_fields_link`
--

CREATE TABLE `zseq_document_type_fields_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_type_fieldsets_link`
--

CREATE TABLE `zseq_document_type_fieldsets_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_document_types_lookup`
--

CREATE TABLE `zseq_document_types_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_documents`
--

CREATE TABLE `zseq_documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_field_behaviours`
--

CREATE TABLE `zseq_field_behaviours` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_field_value_instances`
--

CREATE TABLE `zseq_field_value_instances` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_fieldsets`
--

CREATE TABLE `zseq_fieldsets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_folder_doctypes_link`
--

CREATE TABLE `zseq_folder_doctypes_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_folder_subscriptions`
--

CREATE TABLE `zseq_folder_subscriptions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_folder_transactions`
--

CREATE TABLE `zseq_folder_transactions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_folders`
--

CREATE TABLE `zseq_folders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_folders_users_roles_link`
--

CREATE TABLE `zseq_folders_users_roles_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_groups_groups_link`
--

CREATE TABLE `zseq_groups_groups_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_groups_lookup`
--

CREATE TABLE `zseq_groups_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_help`
--

CREATE TABLE `zseq_help` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_help_replacement`
--

CREATE TABLE `zseq_help_replacement` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_interceptor_instances`
--

CREATE TABLE `zseq_interceptor_instances` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_links`
--

CREATE TABLE `zseq_links` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_metadata_lookup`
--

CREATE TABLE `zseq_metadata_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_metadata_lookup_tree`
--

CREATE TABLE `zseq_metadata_lookup_tree` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_mime_documents`
--

CREATE TABLE `zseq_mime_documents` (
  `id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `zseq_mime_types`
--

CREATE TABLE `zseq_mime_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=162 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_news`
--

CREATE TABLE `zseq_news` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_notifications`
--

CREATE TABLE `zseq_notifications` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_organisations_lookup`
--

CREATE TABLE `zseq_organisations_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_permission_assignments`
--

CREATE TABLE `zseq_permission_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_permission_descriptors`
--

CREATE TABLE `zseq_permission_descriptors` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_permission_dynamic_conditions`
--

CREATE TABLE `zseq_permission_dynamic_conditions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_permission_lookup_assignments`
--

CREATE TABLE `zseq_permission_lookup_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_permission_lookups`
--

CREATE TABLE `zseq_permission_lookups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_permission_objects`
--

CREATE TABLE `zseq_permission_objects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_permissions`
--

CREATE TABLE `zseq_permissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_plugin_rss`
--

CREATE TABLE `zseq_plugin_rss` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_plugins`
--

CREATE TABLE `zseq_plugins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=72 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_role_allocations`
--

CREATE TABLE `zseq_role_allocations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_roles`
--

CREATE TABLE `zseq_roles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_saved_searches`
--

CREATE TABLE `zseq_saved_searches` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_scheduler_tasks`
--

CREATE TABLE `zseq_scheduler_tasks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_search_saved`
--

CREATE TABLE `zseq_search_saved` (
  `id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `zseq_status_lookup`
--

CREATE TABLE `zseq_status_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_system_settings`
--

CREATE TABLE `zseq_system_settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_tag_words`
--

CREATE TABLE `zseq_tag_words` (
  `id` int(10) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `zseq_time_period`
--

CREATE TABLE `zseq_time_period` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_time_unit_lookup`
--

CREATE TABLE `zseq_time_unit_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_units_lookup`
--

CREATE TABLE `zseq_units_lookup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_units_organisations_link`
--

CREATE TABLE `zseq_units_organisations_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_upgrades`
--

CREATE TABLE `zseq_upgrades` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=146 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_user_history`
--

CREATE TABLE `zseq_user_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_users`
--

CREATE TABLE `zseq_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_users_groups_link`
--

CREATE TABLE `zseq_users_groups_link` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_workflow_state_disabled_actions`
--

CREATE TABLE `zseq_workflow_state_disabled_actions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_workflow_state_permission_assignments`
--

CREATE TABLE `zseq_workflow_state_permission_assignments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_workflow_states`
--

CREATE TABLE `zseq_workflow_states` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_workflow_transitions`
--

CREATE TABLE `zseq_workflow_transitions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_workflow_trigger_instances`
--

CREATE TABLE `zseq_workflow_trigger_instances` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `zseq_workflows`
--

CREATE TABLE `zseq_workflows` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-09-25 21:55:41
