-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 22, 2009 at 08:29 AM
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `active_sessions`
--

INSERT INTO `active_sessions` (`id`, `user_id`, `session_id`, `lastused`, `ip`, `apptype`) VALUES
(4, 1, 'k9fi4rebvrh53he7vkj7g9ujo0', '2009-09-22 08:29:09', '127.0.0.1', 'webapp');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `archive_restoration_request`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `archiving_settings`
--


-- --------------------------------------------------------

--
-- Table structure for table `archiving_type_lookup`
--

CREATE TABLE IF NOT EXISTS `archiving_type_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `archiving_type_lookup`
--

INSERT INTO `archiving_type_lookup` (`id`, `name`) VALUES
(1, 'Date'),
(2, 'Utilisation');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `authentication_sources`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `baobab_keys`
--


-- --------------------------------------------------------

--
-- Table structure for table `baobab_scan`
--

CREATE TABLE IF NOT EXISTS `baobab_scan` (
  `checkdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verify` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `baobab_scan`
--

INSERT INTO `baobab_scan` (`checkdate`, `verify`) VALUES
('1981-01-01 00:00:00', 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `baobab_user_keys`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `column_entries`
--

INSERT INTO `column_entries` (`id`, `column_namespace`, `view_namespace`, `config_array`, `position`, `required`) VALUES
(1, 'ktcore.columns.selection', 'ktcore.views.browse', '', 0, 1),
(2, 'ktcore.columns.title', 'ktcore.views.browse', '', 1, 1),
(3, 'ktcore.columns.download', 'ktcore.views.browse', '', 2, 0),
(4, 'ktcore.columns.creationdate', 'ktcore.views.browse', '', 3, 0),
(5, 'ktcore.columns.modificationdate', 'ktcore.views.browse', '', 4, 0),
(6, 'ktcore.columns.creator', 'ktcore.views.browse', '', 5, 0),
(7, 'ktcore.columns.workflow_state', 'ktcore.views.browse', '', 6, 0),
(8, 'ktcore.columns.selection', 'ktcore.views.search', '', 0, 1),
(9, 'ktcore.columns.title', 'ktcore.views.search', '', 1, 1),
(10, 'ktcore.columns.download', 'ktcore.views.search', '', 2, 0),
(11, 'ktcore.columns.creationdate', 'ktcore.views.search', '', 3, 0),
(12, 'ktcore.columns.modificationdate', 'ktcore.views.search', '', 4, 0),
(13, 'ktcore.columns.creator', 'ktcore.views.search', '', 5, 0),
(14, 'ktcore.columns.workflow_state', 'ktcore.views.search', '', 6, 0),
(15, 'ktcore.columns.preview', 'ktcore.views.browse', 'a:0:{}', 2, 0);

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

--
-- Dumping data for table `comment_searchable_text`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `config_groups`
--

INSERT INTO `config_groups` (`id`, `name`, `display_name`, `description`, `category`) VALUES
(1, 'browse', 'Browse View', 'Configurable options for working in Browse View', 'User Interface Settings'),
(2, 'cache', 'Cache', 'Configure settings for the KnowledgeTree cache. Only advanced users should change these settings.', 'General Settings'),
(3, 'CustomErrorMessages', 'Custom Error Messages', 'Configuration settings for custom error messages. Only advanced users should change these settings.', 'User Interface Settings'),
(4, 'dashboard', 'Dashboard', 'Configures Dashboard Settings', 'General Settings'),
(5, 'DiskUsage', 'Disk Usage Dashlet', 'Configures the Disk Usage dashlet ', 'General Settings'),
(6, 'email', 'Email', 'Enables Email on your KnowledgeTree installation and configures Email settings. Note that several KnowledgeTree features use these settings. ', 'Email Settings'),
(7, 'export', 'Export', 'Configures KnowledgeTree''s ''Bulk Export'' feature.', 'General Settings'),
(8, 'externalBinary', 'External Binaries', 'KnowledgeTree uses various external binaries. This section defines the paths to these binaries. <br>Only advanced users should change these settings.', 'General Settings'),
(9, 'i18n', 'Internationalization', 'Configures settings for Internationalization.', 'Internationalisation Settings'),
(10, 'import', 'Import', 'Configures settings on Bulk Import.', 'General Settings'),
(11, 'indexer', 'Document Indexer', 'Configures the Document Indexer. Only advanced users should change these settings.', 'Search and Indexing Settings'),
(12, 'KnowledgeTree', 'KnowledgeTree', 'Configures general settings for your KnowledgeTree server installation.', 'General Settings'),
(13, 'KTWebDAVSettings', 'WebDAV', 'Configuration options for third-party WebDAV clients', 'Client Tools Settings'),
(14, 'openoffice', 'OpenOffice.org Service', 'Configuration options for the OpenOffice.org service. Note that several KnowledgeTree features use this service.', 'Search and Indexing Settings'),
(15, 'search', 'Search', 'Configures settings for KnowledgeTree''s Search function.', 'Search and Indexing Settings'),
(16, 'session', 'Session Management', 'Session management configuration.', 'General Settings'),
(17, 'storage', 'Storage', 'Configure the KnowledgeTree storage manager.', 'General Settings'),
(18, 'tweaks', 'Tweaks', 'Small configuration tweaks', 'General Settings'),
(19, 'ui', 'User Interface', 'General user interface configuration', 'User Interface Settings'),
(20, 'urls', 'Urls', 'The paths to the KnowledgeTree server and filesystem. <br>Full values are specific to your installation (Windows or Linux). Only advanced users should change these settings.', 'General Settings'),
(21, 'user_prefs', 'User Preferences', 'Configures user preferences.', 'General Settings'),
(22, 'webservice', 'Web Services', 'KnowledgeTree Web Service Interface configuration. Note that a number of KnowledgeTree Tools rely on this service.', 'Client Tools Settings'),
(23, 'ldapAuthentication', 'LDAP Authentication', 'Configures LDAP Authentication', 'General Settings'),
(24, 'server', 'Server Settings', 'Configuration settings for the server', 'General Settings'),
(25, 'addInPolicies', 'Office Add-In Policies', 'Configure Central Polices for KnowledgeTree Office Add-In', 'Office Add-In Settings'),
(26, 'BaobabSettings', 'KnowledgeTree Tools Settings', 'KnowledgeTree Tools Server Configuration', 'Client Tools Settings'),
(27, 'clientToolPolicies', 'Client Tools Policies', 'Configure Central Polices for KnowledgeTree Tools', 'Client Tools Settings'),
(28, 'guidInserter', 'GUID Inserter', 'Configuration settings for GUID Inserter', 'General Settings'),
(29, 'e_signatures', 'Electronic Signatures', 'Configuration settings for the electronic signatures', 'Security Settings');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `config_settings`
--

INSERT INTO `config_settings` (`id`, `group_name`, `display_name`, `description`, `item`, `value`, `default_value`, `type`, `options`, `can_edit`) VALUES
(1, 'ui', 'OEM Application Name', 'Specifies the application name used by KnowledgeTree OEM partners. This name replaces ''KnowledgeTree'' wherever the application name displays in the interface.', 'appName', 'KnowledgeTree', 'KnowledgeTree', 'string', NULL, 1),
(2, 'KnowledgeTree', 'Scheduler Interval', 'Defines the frequency, in seconds, at which the Scheduler is set to run.', 'schedulerInterval', 'default', '30', 'numeric_string', NULL, 1),
(3, 'dashboard', 'Always Display ''Your Checked-out Documents''', 'Defines whether to display the ''Your Checked-out Documents'' dashlet, even when there is no data to display. Default is ''False''.', 'alwaysShowYCOD', 'default', 'false', 'boolean', NULL, 1),
(4, 'urls', 'Graphics Url', 'The path to the user interface graphics.', 'graphicsUrl', 'default', '${rootUrl}/graphics', 'string', NULL, 1),
(5, 'urls', 'User Interface Url', 'The path to the core user interface libraries.', 'uiUrl', 'default', '${rootUrl}/presentation/lookAndFeel/knowledgeTree', 'string', NULL, 1),
(6, 'tweaks', 'Browse to Unit Folder', 'Specifies a logged in user''s ''Unit'' folder as their default folder view in Browse Documents. The default, ''False'', displays the root folder.', 'browseToUnitFolder', 'default', 'false', 'boolean', NULL, 1),
(7, 'tweaks', 'Generic Metadata Required', 'Defines whether to present KnowledgeTree''s generic metadata fields for users to fill out on document upload. Default is ''True''.', 'genericMetaDataRequired', 'default', 'true', 'boolean', NULL, 1),
(8, 'tweaks', 'Noisy Bulk Operations', 'Defines whether bulk operations generates a transaction notice on each item, or only on the folder. The default, ''False'' indicates that only folder transactions occur.', 'noisyBulkOperations', 'default', 'false', 'boolean', NULL, 1),
(9, 'tweaks', 'Php Error Log File', 'Enables PHP error logging to the log/php_error_log file. Default is ''False''.', 'phpErrorLogFile', 'default', 'false', 'boolean', NULL, 1),
(10, 'email', 'Email Server', 'The address of the SMTP server. If the host name fails, try the IP address.', 'emailServer', 'none', 'none', '', NULL, 1),
(11, 'email', 'Email Port', 'The port of the SMTP server. The default is 25.', 'emailPort', 'default', '', 'numeric_string', NULL, 1),
(12, 'email', 'Email Authentication', 'Defines whether authentication is required for connecting to SMTP. Default is ''False''. Change to ''True'' to force users to log in using their username and password.', 'emailAuthentication', 'default', 'false', 'boolean', NULL, 1),
(13, 'email', 'Email Username', 'The user name of the SMTP (email) server.', 'emailUsername', 'default', 'username', 'string', NULL, 1),
(14, 'email', 'Email Password', 'The password for the Email server. ', 'emailPassword', 'default', 'password', 'string', NULL, 1),
(15, 'email', 'Email From', 'Defines the sending email address for emails sent from KnowledgeTree.', 'emailFrom', 'default', 'kt@example.org', 'string', NULL, 1),
(16, 'email', 'Email From Name', 'The name used by KnowledgeTree for system-generated emails.', 'emailFromName', 'default', 'KnowledgeTree Document Management System', 'string', NULL, 1),
(17, 'email', 'Allow Attachment', 'Defines whether to allow users to send attachments from within KnowledgeTree. Default is ''False''.', 'allowAttachment', 'default', 'false', 'boolean', NULL, 1),
(18, 'email', 'Allow External Email Addresses', 'Defines whether to allow KnowledgeTree users to send email to any email address - to other KnowledgeTree users and to external users. Default is ''False''.', 'allowEmailAddresses', 'default', 'false', 'boolean', NULL, 1),
(19, 'email', 'Send As System', 'Defines whether to always send email from the KnowledgeTree ''Email From'' address, even if there is an identifiable sending user. Default is ''False''.', 'sendAsSystem', 'default', 'false', 'boolean', NULL, 1),
(20, 'email', 'Only Own Groups', 'Defines whether to restrict users to sending emails only within their KnowledgeTree user group. <br>Default is ''False''. <br>Set to ''True'' to disable sending of emails outside of the user''s group.', 'onlyOwnGroups', 'default', 'false', 'boolean', NULL, 1),
(21, 'user_prefs', 'Password Length', 'Defines the minimum password length on password-setting. ', 'passwordLength', 'default', '6', 'numeric_string', NULL, 1),
(22, 'user_prefs', 'Restrict Admin Passwords', 'Defines whether to require the admin user to apply minimum password length when creating and editing accounts. Default is ''False'', which allows admin users to create accounts with shorter passwords than the specified minimum.', 'restrictAdminPasswords', 'default', 'false', 'boolean', NULL, 1),
(23, 'user_prefs', 'Restrict Preferences', 'Defines whether to restrict users from accessing the Preferences menu. Default is ''False''.', 'restrictPreferences', 'default', 'false', 'boolean', NULL, 1),
(24, 'session', 'Session Timeout', 'Defines the period, in seconds, after which the system times out following a period of inactivity.', 'sessionTimeout', 'default', '1200', 'numeric_string', NULL, 1),
(25, 'session', 'Anonymous Login', 'Defines whether to allow anonymous users to log in automatically. Default is ''False''. <br>Best practice is not to allow automatic login of anonymous users unless you understand KnowledgeTree''s security mechanisms, and have sensibly applied the roles ''Everyone'' and ''Authenticated Users''. ', 'allowAnonymousLogin', 'default', 'false', 'boolean', NULL, 1),
(26, 'ui', 'Company Logo', 'Specifies the path (relative to the KnowledgeTree directory) to the custom logo for the KnowledgeTree user interface. <br>The logo must be 50px tall, and on a white background.', 'companyLogo', 'default', '${rootUrl}/resources/companylogo.png', 'string', NULL, 1),
(27, 'ui', 'Company Logo Width', 'Defines the width, in pixels, of your custom logo.', 'companyLogoWidth', 'default', '313px', 'string', NULL, 1),
(28, 'ui', 'Company Logo Title', 'Alternative text for the title of your custom company logo, for accessibility purposes.', 'companyLogoTitle', 'default', 'Add Company Name', 'string', NULL, 1),
(29, 'ui', 'Always Show All Results', 'Defines, where ''show all users'' is an available action, whether to display the full list of users and groups on page load, without requiring the user to click ''show all users''. Default is ''False''.', 'alwaysShowAll', 'default', 'false', 'boolean', NULL, 1),
(30, 'ui', 'Condensed Admin UI', 'Defines whether to use a condensed (compact) version of the KnowledgeTree user interface for the admin user. Default is ''False''.', 'condensedAdminUI', 'default', 'false', 'boolean', NULL, 1),
(31, 'ui', 'Fake Mimetype', 'Defines whether browsers may provide the option to ''open'' a document from download. Default is ''False''.<br>Change to ''True'' to prevent (most) browsers from giving users the ''Open'' option.', 'fakeMimetype', 'default', 'false', 'boolean', NULL, 1),
(32, 'i18n', 'UseLike', 'Enables ''search ideographic language'' on languages that do not have distinguishable words (typically, where there is no space character), and allows KnowledgeTree''s Search function to deal with this issue. Default is ''False''.', 'useLike', 'default', 'false', 'boolean', NULL, 1),
(33, 'import', 'unzip', 'Specifies the location of the unzip binary. The unzip command uses ''execSearchPath'' to find the unzip binary if the path is not provided. Values are auto-populated, specific to your installation (Windows or Linux).', 'unzip', 'default', 'unzip', 'string', NULL, 1),
(34, 'export', 'zip', 'The location of the zip binary. <br>The zip command uses ''execSearchPath'' to find the zip binary if the path is not provided. Values are auto-populated, specific to your installation (Windows or Linux).', 'zip', 'default', 'zip', 'string', NULL, 1),
(35, 'externalBinary', 'xls2csv', 'Path to binary', 'xls2csv', 'default', 'xls2csv', 'string', NULL, 1),
(36, 'externalBinary', 'pdftotext', 'Path to binary', 'pdftotext', 'default', 'pdftotext', 'string', NULL, 1),
(37, 'externalBinary', 'catppt', 'Path to binary', 'catppt', 'default', 'catppt', 'string', NULL, 1),
(38, 'externalBinary', 'pstotext', 'Path to binary', 'pstotext', 'default', 'pstotext', 'string', NULL, 1),
(39, 'externalBinary', 'catdoc', 'Path to binary', 'catdoc', 'default', 'catdoc', 'string', NULL, 1),
(40, 'externalBinary', 'antiword', 'Path to binary', 'antiword', 'default', 'antiword', 'string', NULL, 1),
(41, 'externalBinary', 'python', 'Path to binary', 'python', 'default', 'python', 'string', NULL, 1),
(42, 'externalBinary', 'java', 'Path to binary', 'java', 'default', 'java', 'string', NULL, 1),
(43, 'externalBinary', 'php', 'Path to binary', 'php', 'default', 'php', 'string', NULL, 1),
(44, 'externalBinary', 'df', 'Path to binary', 'df', 'default', 'df', 'string', NULL, 1),
(45, 'cache', 'Proxy Cache Path', 'The path to the proxy cache. Default is <var directory>/cache.', 'proxyCacheDirectory', 'default', '${varDirectory}/proxies', 'string', NULL, 1),
(46, 'cache', 'Proxy Cache Enabled', 'Enables proxy caching. Default is ''True''. ', 'proxyCacheEnabled', 'default', 'true', 'boolean', NULL, 1),
(47, 'KTWebDAVSettings', 'Debug', 'Switch debug output to ''on'' only if you must view ''all'' debugging information for KTWebDAV. The default is ''off''.', 'debug', 'off', 'off', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(48, 'KTWebDAVSettings', 'Safemode', 'To allow ''write'' access to WebDAV clients, set safe mode to "off". The default is ''on''.', 'safemode', 'on', 'on', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(49, 'search', 'Search Base', 'The location of the Search and Indexing libraries.', 'searchBasePath', 'default', '${fileSystemRoot}/search2', 'string', NULL, 0),
(50, 'search', 'Fields Path', 'The location of the Search and Indexing fields.', 'fieldsPath', 'default', '${searchBasePath}/search/fields', 'string', NULL, 0),
(51, 'search', 'Results Display Format', 'Defines how search results display. Options are: search engine style, or browse view style. The default is ''Search Engine Style''.', 'resultsDisplayFormat', 'default', 'searchengine', 'dropdown', 'a:1:{s:7:"options";a:2:{i:0;a:2:{s:5:"label";s:19:"Search Engine Style";s:5:"value";s:12:"searchengine";}i:1;a:2:{s:5:"label";s:17:"Browse View Style";s:5:"value";s:10:"browseview";}}}', 1),
(52, 'search', 'Results per Page', 'The number of results to display per page.', 'resultsPerPage', 'default', '25', 'numeric_string', NULL, 1),
(53, 'search', 'Date Format', 'The date format used when making queries using widgets.', 'dateFormat', 'default', 'Y-m-d', 'string', NULL, 0),
(54, 'browse', 'Property Preview Activation', 'Defines the action for displaying the Property Preview. Options are ''On Click'' or ''Mouseover''. Default is ''On Click''.', 'previewActivation', 'default', 'onclick', 'dropdown', 'a:1:{s:7:"options";a:2:{i:0;a:2:{s:5:"label";s:9:"Mouseover";s:5:"value";s:10:"mouse-over";}i:1;a:2:{s:5:"label";s:8:"On Click";s:5:"value";s:7:"onclick";}}}', 1),
(55, 'indexer', 'Core Class', 'Defines the core indexing class. Options include: JavaXMLRPCLuceneIndexer or PHPLuceneIndexer.', 'coreClass', 'default', 'JavaXMLRPCLuceneIndexer', 'string', NULL, 0),
(56, 'indexer', 'Batch Documents', 'The number of documents to be indexed in a cron session. ', 'batchDocuments', 'default', '20', 'numeric_string', 'a:3:{s:9:"increment";i:10;s:7:"minimum";i:20;s:7:"maximum";i:200;}', 1),
(57, 'indexer', 'Batch Migrate Documents', 'The number of documents to be migrated in a cron session, using KnowledgeTree''s migration script. ', 'batchMigrateDocuments', 'default', '500', 'numeric_string', NULL, 1),
(58, 'indexer', 'Indexing Base ', 'The location of the Indexing engine.', 'indexingBasePath', 'default', '${searchBasePath}/indexing', 'string', NULL, 0),
(59, 'indexer', 'Lucene Directory', 'The location of the Lucene indexes.', 'luceneDirectory', 'default', '${varDirectory}/indexes', 'string', NULL, 0),
(60, 'indexer', 'Extractors ', 'The location of the text extractors.', 'extractorPath', 'default', '${indexingBasePath}/extractors', 'string', NULL, 0),
(61, 'indexer', 'Extractor Hook ', 'The location of the extractor hooks.', 'extractorHookPath', 'default', '${indexingBasePath}/extractorHooks', 'string', NULL, 0),
(62, 'indexer', 'Java Lucene Server ', 'The location (URL) of the Java Lucene server. Ensure that this matches the Lucene server configuration. ', 'javaLuceneURL', 'default', 'http://127.0.0.1:8875', 'string', NULL, 0),
(63, 'openoffice', 'Host', 'Defines the host on which OpenOffice is installed. Ensure that this points to the OpenOffice server. ', 'host', 'default', '127.0.0.1', 'string', NULL, 1),
(64, 'openoffice', 'Port', 'Defines the port on which OpenOffice listens. ', 'port', 'default', '8100', 'numeric_string', NULL, 1),
(65, 'webservice', 'Upload Directory', 'Directory to which all uploads via webservices are persisted before moving into the repository.', 'uploadDirectory', '/var/www/installers/knowledgetree/var/uploads', '${varDirectory}/uploads', 'string', NULL, 1),
(66, 'webservice', 'Download Url', 'Url which is sent to clients via web service calls so they can then download file via HTTP GET.', 'downloadUrl', 'default', '${rootUrl}/ktwebservice/download.php', 'string', NULL, 1),
(67, 'webservice', 'Upload Expiry', 'Period indicating how long a file should be retained in the uploads directory.', 'uploadExpiry', 'default', '30', 'numeric_string', 'a:1:{s:6:"append";s:7:"seconds";}', 1),
(68, 'webservice', 'Download Expiry', 'Period indicating how long a download link will be available.', 'downloadExpiry', 'default', '30', 'numeric_string', 'a:1:{s:6:"append";s:7:"seconds";}', 1),
(69, 'webservice', 'Random Key Text', 'Random text used to construct a hash. This can be customised on installations so there is less chance of overlap between installations.', 'randomKeyText', 'default', 'bkdfjhg23yskjdhf2iu', 'string', NULL, 1),
(70, 'webservice', 'Validate Session Count', 'Validating session counts can interfere with access. It is best to leave this disabled, unless very strict access is required.', 'validateSessionCount', 'false', 'false', 'boolean', NULL, 1),
(71, 'webservice', 'Use Default Document Type If Invalid', 'If the document type is invalid when adding a document, we can be tollerant and just default to the Default document type.', 'useDefaultDocumentTypeIfInvalid', 'true', 'true', 'boolean', NULL, 1),
(72, 'webservice', 'Debug', 'The web service debugging if the logLevel is set to DEBUG. We can set the value to 4 or 5 to get more verbose web service logging. Level 4 logs the name of functions being accessed. Level 5 logs the SOAP XML requests and responses.', 'debug', '0', '0', 'numeric_string', NULL, 1),
(73, 'DiskUsage', 'Warning Threshold', 'The percentage below which the mount in the Disk Usage dashlet changes to Orange, indicating that the mount point is running out of free space. ', 'warningThreshold', '10', '10', 'numeric_string', 'a:1:{s:6:"append";s:1:"%";}', 1),
(74, 'DiskUsage', 'Urgent Threshold', 'The percentage below which the mount in the Disk Usage dashlet changes to Red, indicating that the lack of free space in the mount is critically low.', 'urgentThreshold', '5', '5', 'numeric_string', 'a:1:{s:6:"append";s:1:"%";}', 1),
(75, 'KnowledgeTree', 'Use AJAX Dashboard', 'Defines whether to use the AJAX dashboard, which allows users to drag the dashlets to change the Dashboard display.<br>Default is ''True''. ', 'useNewDashboard', 'true', 'true', 'boolean', NULL, 1),
(76, 'i18n', 'Default Language', 'Defines the default language for the KnowledgeTree user interface. The default is English (en).', 'defaultLanguage', 'default', 'en', 'string', NULL, 1),
(77, 'CustomErrorMessages', 'Custom Error Messages', 'Enables and disables custom error messages. Default is ''On'' (enabled).', 'customerrormessages', 'default', 'on', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(78, 'CustomErrorMessages', 'Custom Error Page Path', 'The file name or URL of the custom error page.', 'customerrorpagepath', 'default', 'customerrorpage.php', 'string', NULL, 1),
(79, 'CustomErrorMessages', 'Custom Error Handler', 'Enables and disables the custom error handler feature. Default is ''On'' (enabled).', 'customerrorhandler', 'default', 'on', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(80, 'ui', 'Enable Custom Skinning', 'Defines whether customs skins may be used for the KnowledgeTree user interface. Default is ''False''.', 'morphEnabled', 'default', 'false', 'boolean', NULL, 1),
(81, 'ui', 'Default Skin', 'Defines, when skinning is enabled, the location of the custom skin to use for the KnowledgeTree user interface.', 'morphTo', 'default', 'blue', 'string', NULL, 1),
(82, 'KnowledgeTree', 'Log Level', 'Defines the level of logging to use (DEBUG, INFO, WARN, ERROR). The default is INFO.', 'logLevel', 'default', 'INFO', 'dropdown', 'a:1:{s:7:"options";a:4:{i:0;a:2:{s:5:"label";s:4:"INFO";s:5:"value";s:4:"INFO";}i:1;a:2:{s:5:"label";s:4:"WARN";s:5:"value";s:4:"WARN";}i:2;a:2:{s:5:"label";s:5:"ERROR";s:5:"value";s:5:"ERROR";}i:3;a:2:{s:5:"label";s:5:"DEBUG";s:5:"value";s:5:"DEBUG";}}}', 1),
(83, 'storage', 'Manager', 'Defines the storage manager to use for storing documents on the file system. ', 'manager', 'default', 'KTOnDiskHashedStorageManager', 'string', NULL, 1),
(84, 'ui', 'IE GIF Theme Overrides', 'Defines whether to use the additional IE-specific GIF theme overrides, which may restrict <br>the working of arbitrary theme packs without having GIF versions available. Default is ''False''.', 'ieGIF', 'false', 'true', 'boolean', NULL, 1),
(85, 'ui', 'Automatic Refresh', 'Set to true to automatically refresh the page after the session would have expired.', 'automaticRefresh', 'default', 'false', 'boolean', NULL, 1),
(86, 'ui', 'dot', 'Location of the dot binary (command location). On Unix systems, to determine whether the ''dot'' application is installed.', 'dot', 'dot', 'dot', 'string', NULL, 1),
(87, 'urls', 'Log Directory', 'The path to the Log directory.', 'logDirectory', '/var/www/installers/knowledgetree/var/log', '${varDirectory}/log', 'string', NULL, 1),
(88, 'urls', 'UI Directory', 'The path to the UI directory.', 'uiDirectory', 'default', '${fileSystemRoot}/presentation/lookAndFeel/knowledgeTree', 'string', NULL, 1),
(89, 'urls', 'Temp Directory', 'The path to the temp directory.', 'tmpDirectory', '/var/www/installers/knowledgetree/var/tmp', '${varDirectory}/tmp', 'string', NULL, 1),
(90, 'urls', 'Stopwords File', 'The path to the stopword file.', 'stopwordsFile', 'default', '${fileSystemRoot}/config/stopwords.txt', 'string', NULL, 1),
(91, 'cache', 'Cache Enabled', 'Enables the KnowledgeTree cache. Default is ''False''.', 'cacheEnabled', 'default', 'false', 'boolean', NULL, 1),
(92, 'cache', 'Cache Directory', 'The location of the KnowledgeTree cache.', 'cacheDirectory', 'default', '${varDirectory}/cache', 'string', NULL, 1),
(93, 'openoffice', 'Program Path', 'Defines the path to the OpenOffice program directory. ', 'programPath', 'default', '../openoffice/program', 'string', NULL, 1),
(94, 'urls', 'Document Directory', 'The path to the documents directory', 'documentRoot', '/var/www/installers/knowledgetree/var/Documents', '${varDirectory}/Documents', 'string', NULL, 1),
(95, 'KnowledgeTree', 'Redirect To Browse View', 'Defines whether to redirect to the Browse view (Browse Documemts) on login, instead of the Dashboard.<br>Default is ''False''. ', 'redirectToBrowse', 'default', 'false', 'boolean', NULL, 1),
(96, 'KnowledgeTree', 'Redirect To Browse View: Exceptions', 'Specifies that, when ''Redirect To Browse'' is set to ''True'' all users, except for the users listed in the text field below are redirected to the Browse view on log in. The users listed for this setting are directed to the KnowledgeTree Dashboard. To define exceptions, add user names in the text field as follows, e.g. admin, joebloggs, etc.', 'redirectToBrowseExceptions', '', '', 'string', NULL, 1),
(97, 'session', 'Allow Automatic Sign In', 'Defines whether to automatically create a user account on first login for any user who does not yet exist in the system. Default is ''False''.', 'allowAutoSignup', 'default', 'false', 'boolean', 'string', 1),
(98, 'ldapAuthentication', 'Create Groups Automatically', 'Defines whether to allow LDAP groups to be created automatically. Default is ''False''.', 'autoGroupCreation', 'default', 'false', 'boolean', 'string', 1),
(99, 'browse', 'Truncate Document and Folder Titles in Browse View', 'Defines the maximum number of characters to display for a document or folder title in the browse view. The maximum allowable number of characters is 255.', 'titleCharLength', 'default', '40', 'numeric_string', 'string', 1),
(100, 'import', 'Disable Bulk Import', 'Disable the bulk import plugin', 'disableBulkImport', 'default', 'false', 'string', NULL, 1),
(101, 'session', 'Enable version check', 'Compares the system version with the database version to determine if a database upgrade is needed.', 'dbversioncompare', 'default', 'true', 'boolean', NULL, 0),
(102, 'tweaks', 'Update Document Version (Content) on Editing Metadata', 'The document version is equivalent to the document content version. When set to true the document version will be increased when the document metadata is updated.', 'updateContentVersion', 'default', 'false', 'boolean', NULL, 1),
(103, 'tweaks', 'Always Force Original Filename on Checkin', 'When set to true, the checkbox for "Force Original Filename" will be hidden on check-in. This ensures that the filename will always stay the same.', 'disableForceFilenameOption', 'default', 'false', 'boolean', NULL, 1),
(104, 'KnowledgeTree', 'The Location of the Mime Magic File', 'The path to the mime magic database file.', 'magicDatabase', 'default', '${fileSystemRoot}/../common/share/file/magic', 'string', NULL, 1),
(105, 'search', 'Maximum results from SQL query', 'The maximum results from an SQL query', 'maxSqlResults', 'default', '10000', 'numeric_string', NULL, 1),
(106, 'indexer', 'Enable the Document Indexer', 'Enables the indexing of document content for full text searching.', 'enableIndexing', 'default', 'true', 'boolean', NULL, 1),
(107, 'server', 'Internal Server IP', 'The internal IP for the server, this is usually set to 127.0.0.1.', 'internal_server_name', 'default', '127.0.0.1', 'string', NULL, 1),
(108, 'server', 'Internal Server port', 'The internal port for the server.', 'internal_server_port', 'default', '80', 'numeric_string', NULL, 1),
(109, 'server', 'External Server IP', 'The external IP for the server.', 'server_name', '127.0.0.1', '', 'string', NULL, 1),
(110, 'server', 'External Server port', 'The external port for the server.', 'server_port', '80', '', 'numeric_string', NULL, 1),
(111, 'KnowledgeTree', 'Root Url', 'The path to the web application from the root of the web server. For example, if KT is at http://example.org/foo/, then the root directory should be ''/foo''.', 'rootUrl', '/installers/knowledgetree', '', 'string', NULL, 1),
(112, 'urls', 'Var Directory', 'The path to the var directory.', 'varDirectory', '/var/www/installers/knowledgetree/var', '${fileSystemRoot}/var', 'string', NULL, 1),
(113, 'tweaks', 'Increment version on rename', 'Defines whether to update the version number if a document filename is changed/renamed.', 'incrementVersionOnRename', 'default', 'true', 'boolean', NULL, 1),
(114, 'ui', 'System URL', 'The system url, used in the main logo.', 'systemUrl', 'default', 'http://www.knowledgetree.com', 'string', '', 1),
(115, 'ldapAuthentication', 'Allow Moving Users in LDAP/AD', 'Moving users around within the LDAP or Active Directory structure will cause failed logins for these users. When this setting is enabled, a failed login will trigger a search for the user using their sAMAccountName setting and update their authentication details.', 'enableLdapUpdate', 'default', 'false', 'boolean', NULL, 1),
(116, 'export', 'Use External Zip Binary', 'Utilises the external zip binary for compressing archives. The default is to use the PEAR archive class.', 'useBinary', 'default', 'false', 'boolean', NULL, 1),
(117, 'export', 'Use Bulk Download Queue', 'The bulk download can be large and can prevent normal browsing. The download queue performs the bulk downloads in the background.', 'useDownloadQueue', 'default', 'true', 'boolean', NULL, 1),
(118, 'urls', 'PDF Directoy', 'The path for storing the generated PDF Documents', 'pdfDirectory', 'default', '${varDirectory}/Pdf', 'string', '', 1),
(119, 'addInPolicies', 'Capture Reasons: Check in', 'Defines whether a reason is required on ''check in'' action in KnowledgeTree Office Add-In for Windows. Default is ''True''.', 'captureReasonsCheckin', 'true', 'true', 'boolean', NULL, 1),
(120, 'addInPolicies', 'Capture Reasons: Check-out', 'Defines whether a reason is required on ''check-out'' action in KnowledgeTree Office Add-In for Windows. Default is ''True''.', 'captureReasonsCheckout', 'true', 'true', 'boolean', NULL, 1),
(121, 'addInPolicies', 'Allow: Remember Session', 'Defines whether passwords may be stored on the client. Default is ''True''.', 'allowRememberPassword', 'true', 'true', 'boolean', NULL, 1),
(122, 'BaobabSettings', 'Debug', 'Switch debug output to ''on'' only if you must view ''all'' debugging information. The default is ''off''.', 'debug', 'off', 'off', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(123, 'BaobabSettings', 'Safemode', 'To allow write access to WebDAV clients, set safe mode to ''off''. Default is ''on''.', 'safemode', 'on', 'on', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(124, 'clientToolPolicies', 'Explorer: Metadata Capture', 'Defines whether the client is prompted for metadata when adding a document through KnowledgeTree Explorer. Default is ''True''.', 'explorerMetadataCapture', 'true', 'true', 'boolean', NULL, 1),
(125, 'clientToolPolicies', 'Office: Metadata Capture', 'Defines whether the client is prompted for metadata when adding a document to KnowledgeTree from within Microsoft Office. Default is ''True''.', 'officeMetadataCapture', 'true', 'true', 'boolean', NULL, 1),
(126, 'clientToolPolicies', 'Capture Reasons: Delete', 'Defines whether a reason is required on ''delete'' action in KnowledgeTree Client Tools for Windows. Default is ''True''.', 'captureReasonsDelete', 'true', 'true', 'boolean', NULL, 1),
(127, 'clientToolPolicies', 'Capture Reasons: Check in', 'Defines whether a reason is required on ''check in'' action in KnowledgeTree Client Tools for Windows. Default is ''True''.', 'captureReasonsCheckin', 'true', 'true', 'boolean', NULL, 1),
(128, 'clientToolPolicies', 'Capture Reasons: Check-out', 'Defines whether a reason is required on ''check-out'' action in KnowledgeTree Client Tools for Windows. Default is ''True''.', 'captureReasonsCheckout', 'true', 'true', 'boolean', NULL, 1),
(129, 'clientToolPolicies', 'Capture Reasons: Cancel Check-out', 'Defines whether a reason is required on ''cancel check-out'' action in KnowledgeTree Client Tools for Windows. Default is ''True''.', 'captureReasonsCancelCheckout', 'true', 'true', 'boolean', NULL, 1),
(130, 'clientToolPolicies', 'Capture Reasons: Copy', 'Defines whether a reason is required on ''copy'' action in KnowledgeTree Client Tools for Windows. Default is ''True''.', 'captureReasonsCopyInKT', 'true', 'true', 'boolean', NULL, 1),
(131, 'clientToolPolicies', 'Capture Reasons: Move', 'Defines whether a reason is required on ''move'' action in KnowledgeTree Client Tools for Windows. Default is ''True''.', 'captureReasonsMoveInKT', 'true', 'true', 'boolean', NULL, 1),
(132, 'clientToolPolicies', 'Allow Remember Password', 'Defines whether passwords may be stored on the client. Default is ''True''.', 'allowRememberPassword', 'true', 'true', 'boolean', NULL, 1),
(133, 'guidInserter', 'Backup latest content version', 'Defines whether to backup latest content version prior to GUID Insert', 'doBackup', 'default', 'true', 'boolean', NULL, 1),
(134, 'e_signatures', 'Enable Electronic Signatures', 'Enables the electronic signature functionality on write actions.', 'enableESignatures', 'default', 'false', 'boolean', '', 1),
(135, 'e_signatures', 'Enable Administrative Electronic Signature', 'Enables the electronic signature functionality for accessing the Administrative section.', 'enableAdminSignatures', 'default', 'false', 'boolean', '', 1),
(136, 'e_signatures', 'Enable API Electronic Signatures', 'Enables the electronic signature functionality in the API and for all client tools.', 'enableApiSignatures', 'default', 'false', 'boolean', '', 1),
(137, 'e_signatures', 'Set Time Interval for Administrative Electronic Signature', 'Sets the time-interval (in seconds) before re-authentication is required in the administrative section', 'adminSignatureTime', 'default', '600', 'numeric_string', '', 1),
(138, 'urls', 'PDF Directoy', 'The path for storing the generated PDF Documents', 'pdfDirectory', 'default', '${varDirectory}/Pdf', 'string', '', 1);

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

--
-- Dumping data for table `custom_sequences`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `dashlet_disables`
--


-- --------------------------------------------------------

--
-- Table structure for table `data_types`
--

CREATE TABLE IF NOT EXISTS `data_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `data_types`
--

INSERT INTO `data_types` (`id`, `name`) VALUES
(1, 'STRING'),
(2, 'CHAR'),
(3, 'TEXT'),
(4, 'INT'),
(5, 'FLOAT'),
(6, 'LARGE TEXT'),
(7, 'DATE');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `discussion_comments`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `discussion_threads`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `documents`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_alerts`
--


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

--
-- Dumping data for table `document_alerts_users`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_archiving_link`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `document_content_version`
--


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
  `has_inetlookup` tinyint(1) DEFAULT NULL,
  `inetlookup_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_fieldset` (`parent_fieldset`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_fields`
--

INSERT INTO `document_fields` (`id`, `name`, `data_type`, `is_generic`, `has_lookup`, `has_lookuptree`, `parent_fieldset`, `is_mandatory`, `description`, `position`, `is_html`, `max_length`, `has_inetlookup`, `inetlookup_type`) VALUES
(2, 'Tag', 'STRING', 0, 0, 0, 2, 0, 'Tag Words', 0, NULL, NULL, NULL, NULL),
(3, 'Document Author', 'STRING', 0, 0, 0, 3, 0, 'Please add a document author', 0, NULL, NULL, NULL, NULL),
(4, 'Category', 'STRING', 0, 1, 0, 3, 0, 'Please select a category', 1, NULL, NULL, NULL, NULL),
(5, 'Media Type', 'STRING', 0, 1, 0, 3, 0, 'Please select a media type', 2, NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_fields_link`
--


-- --------------------------------------------------------

--
-- Table structure for table `document_incomplete`
--

CREATE TABLE IF NOT EXISTS `document_incomplete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contents` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `metadata` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_incomplete`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_link`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_link_types`
--

INSERT INTO `document_link_types` (`id`, `name`, `reverse_name`, `description`) VALUES
(-1, 'depended on', 'was depended on by', 'Depends relationship whereby one documents depends on another''s creation to go through approval'),
(0, 'Default', 'Default (reverse)', 'Default link type'),
(3, 'Attachment', '', 'Document Attachment'),
(4, 'Reference', '', 'Document Reference'),
(5, 'Copy', '', 'Document Copy');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_metadata_version`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_role_allocations`
--


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

--
-- Dumping data for table `document_searchable_text`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `document_subscriptions`
--


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

--
-- Dumping data for table `document_tags`
--


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

--
-- Dumping data for table `document_text`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_transactions`
--


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

--
-- Dumping data for table `document_transaction_text`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_transaction_types_lookup`
--

INSERT INTO `document_transaction_types_lookup` (`id`, `name`, `namespace`) VALUES
(1, 'Create', 'ktcore.transactions.create'),
(2, 'Update', 'ktcore.transactions.update'),
(3, 'Delete', 'ktcore.transactions.delete'),
(4, 'Rename', 'ktcore.transactions.rename'),
(5, 'Move', 'ktcore.transactions.move'),
(6, 'Download', 'ktcore.transactions.download'),
(7, 'Check In', 'ktcore.transactions.check_in'),
(8, 'Check Out', 'ktcore.transactions.check_out'),
(9, 'Collaboration Step Rollback', 'ktcore.transactions.collaboration_step_rollback'),
(10, 'View', 'ktcore.transactions.view'),
(11, 'Expunge', 'ktcore.transactions.expunge'),
(12, 'Force CheckIn', 'ktcore.transactions.force_checkin'),
(13, 'Email Link', 'ktcore.transactions.email_link'),
(14, 'Collaboration Step Approve', 'ktcore.transactions.collaboration_step_approve'),
(15, 'Email Attachment', 'ktcore.transactions.email_attachment'),
(16, 'Workflow state transition', 'ktcore.transactions.workflow_state_transition'),
(17, 'Permissions changed', 'ktcore.transactions.permissions_change'),
(18, 'Role allocations changed', 'ktcore.transactions.role_allocations_change'),
(19, 'Bulk Export', 'ktstandard.transactions.bulk_export'),
(20, 'Copy', 'ktcore.transactions.copy'),
(21, 'Delete Version', 'ktcore.transactions.delete_version'),
(22, 'Alert added / modified', 'alerts.transactions.alert'),
(23, 'Document type alert added / modified', 'alerts.transactions.type_alert');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_types_lookup`
--

INSERT INTO `document_types_lookup` (`id`, `name`, `disabled`, `scheme`, `regen_on_checkin`) VALUES
(1, 'Default', 0, '<DOCID>', 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_type_alerts`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_type_fieldsets_link`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `document_type_fields_link`
--


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

--
-- Dumping data for table `download_files`
--


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

--
-- Dumping data for table `download_queue`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `fieldsets`
--

INSERT INTO `fieldsets` (`id`, `name`, `namespace`, `mandatory`, `is_conditional`, `master_field`, `is_generic`, `is_complex`, `is_complete`, `is_system`, `description`, `disabled`) VALUES
(2, 'Tag Cloud', 'tagcloud', 0, 0, NULL, 1, 0, 0, 0, 'Tag Cloud', 0),
(3, 'General information', 'generalinformation', 0, 0, NULL, 1, 0, 0, 0, 'General document information', 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `field_behaviours`
--


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

--
-- Dumping data for table `field_behaviour_options`
--


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

--
-- Dumping data for table `field_orders`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `field_value_instances`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `name`, `description`, `parent_id`, `creator_id`, `created`, `modified_user_id`, `modified`, `is_public`, `parent_folder_ids`, `full_path`, `permission_object_id`, `permission_lookup_id`, `restrict_document_types`, `owner_id`, `linked_folder_id`) VALUES
(1, 'Root Folder', 'Root Folder', NULL, 1, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', 0, NULL, NULL, 1, 5, 0, 1, NULL),
(2, 'DroppedDocuments', 'DroppedDocuments', 1, 1, '2009-09-22 08:29:09', 1, '2009-09-22 08:29:09', 0, '1', 'DroppedDocuments', 2, 8, 0, 1, NULL),
(3, 'admin', 'admin', 2, 1, '2009-09-22 08:29:10', 1, '2009-09-22 08:29:10', 0, '1,2', 'DroppedDocuments/admin', 3, 10, 0, 1, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `folders_users_roles_link`
--


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

--
-- Dumping data for table `folder_descendants`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `folder_doctypes_link`
--

INSERT INTO `folder_doctypes_link` (`id`, `folder_id`, `document_type_id`) VALUES
(1, 1, 1);

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

--
-- Dumping data for table `folder_searchable_text`
--

INSERT INTO `folder_searchable_text` (`folder_id`, `folder_text`) VALUES
(1, 'Root Folder');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `folder_subscriptions`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `folder_transactions`
--

INSERT INTO `folder_transactions` (`id`, `folder_id`, `user_id`, `datetime`, `ip`, `comment`, `transaction_namespace`, `session_id`, `admin_mode`) VALUES
(1, 2, 1, '2009-09-22 08:29:09', '127.0.0.1', 'Folder created', 'ktcore.transactions.create', 4, 0),
(2, 3, 1, '2009-09-22 08:29:10', '127.0.0.1', 'Folder created', 'ktcore.transactions.create', 4, 0);

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

--
-- Dumping data for table `folder_workflow_map`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `groups_groups_link`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `groups_lookup`
--

INSERT INTO `groups_lookup` (`id`, `name`, `is_sys_admin`, `is_unit_admin`, `unit_id`, `authentication_details_s2`, `authentication_details_s1`, `authentication_source_id`) VALUES
(1, 'System Administrators', 1, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `help`
--

CREATE TABLE IF NOT EXISTS `help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fSection` varchar(100) NOT NULL DEFAULT '',
  `help_info` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `help`
--

INSERT INTO `help` (`id`, `fSection`, `help_info`) VALUES
(1, 'browse', 'dochelp.html'),
(2, 'dashboard', 'dashboardHelp.html'),
(3, 'addFolder', 'addFolderHelp.html'),
(4, 'editFolder', 'editFolderHelp.html'),
(5, 'addFolderCollaboration', 'addFolderCollaborationHelp.html'),
(6, 'modifyFolderCollaboration', 'addFolderCollaborationHelp.html'),
(7, 'addDocument', 'addDocumentHelp.html'),
(8, 'viewDocument', 'viewDocumentHelp.html'),
(9, 'modifyDocument', 'modifyDocumentHelp.html'),
(10, 'modifyDocumentRouting', 'modifyDocumentRoutingHelp.html'),
(11, 'emailDocument', 'emailDocumentHelp.html'),
(12, 'deleteDocument', 'deleteDocumentHelp.html'),
(13, 'administration', 'administrationHelp.html'),
(14, 'addGroup', 'addGroupHelp.html'),
(15, 'editGroup', 'editGroupHelp.html'),
(16, 'removeGroup', 'removeGroupHelp.html'),
(17, 'assignGroupToUnit', 'assignGroupToUnitHelp.html'),
(18, 'removeGroupFromUnit', 'removeGroupFromUnitHelp.html'),
(19, 'addUnit', 'addUnitHelp.html'),
(20, 'editUnit', 'editUnitHelp.html'),
(21, 'removeUnit', 'removeUnitHelp.html'),
(22, 'addOrg', 'addOrgHelp.html'),
(23, 'editOrg', 'editOrgHelp.html'),
(24, 'removeOrg', 'removeOrgHelp.html'),
(25, 'addRole', 'addRoleHelp.html'),
(26, 'editRole', 'editRoleHelp.html'),
(27, 'removeRole', 'removeRoleHelp.html'),
(28, 'addLink', 'addLinkHelp.html'),
(29, 'addLinkSuccess', 'addLinkHelp.html'),
(30, 'editLink', 'editLinkHelp.html'),
(31, 'removeLink', 'removeLinkHelp.html'),
(32, 'systemAdministration', 'systemAdministrationHelp.html'),
(33, 'deleteFolder', 'deleteFolderHelp.html'),
(34, 'editDocType', 'editDocTypeHelp.html'),
(35, 'removeDocType', 'removeDocTypeHelp.html'),
(36, 'addDocType', 'addDocTypeHelp.html'),
(37, 'addDocTypeSuccess', 'addDocTypeHelp.html'),
(38, 'manageSubscriptions', 'manageSubscriptionsHelp.html'),
(39, 'addSubscription', 'addSubscriptionHelp.html'),
(40, 'removeSubscription', 'removeSubscriptionHelp.html'),
(41, 'preferences', 'preferencesHelp.html'),
(42, 'editPrefsSuccess', 'preferencesHelp.html'),
(43, 'modifyDocumentGenericMetaData', 'modifyDocumentGenericMetaDataHelp.html'),
(44, 'viewHistory', 'viewHistoryHelp.html'),
(45, 'checkInDocument', 'checkInDocumentHelp.html'),
(46, 'checkOutDocument', 'checkOutDocumentHelp.html'),
(47, 'advancedSearch', 'advancedSearchHelp.html'),
(48, 'deleteFolderCollaboration', 'deleteFolderCollaborationHelp.html'),
(49, 'addFolderDocType', 'addFolderDocTypeHelp.html'),
(50, 'deleteFolderDocType', 'deleteFolderDocTypeHelp.html'),
(51, 'addGroupFolderLink', 'addGroupFolderLinkHelp.html'),
(52, 'deleteGroupFolderLink', 'deleteGroupFolderLinkHelp.html'),
(53, 'addWebsite', 'addWebsiteHelp.html'),
(54, 'addWebsiteSuccess', 'addWebsiteHelp.html'),
(55, 'editWebsite', 'editWebsiteHelp.html'),
(56, 'removeWebSite', 'removeWebSiteHelp.html'),
(57, 'standardSearch', 'standardSearchHelp.html'),
(58, 'modifyDocumentTypeMetaData', 'modifyDocumentTypeMetaDataHelp.html'),
(59, 'addDocField', 'addDocFieldHelp.html'),
(60, 'editDocField', 'editDocFieldHelp.html'),
(61, 'removeDocField', 'removeDocFieldHelp.html'),
(62, 'addMetaData', 'addMetaDataHelp.html'),
(63, 'editMetaData', 'editMetaDataHelp.html'),
(64, 'removeMetaData', 'removeMetaDataHelp.html'),
(65, 'addUser', 'addUserHelp.html'),
(66, 'editUser', 'editUserHelp.html'),
(67, 'removeUser', 'removeUserHelp.html'),
(68, 'addUserToGroup', 'addUserToGroupHelp.html'),
(69, 'removeUserFromGroup', 'removeUserFromGroupHelp.html'),
(70, 'viewDiscussion', 'viewDiscussionThread.html'),
(71, 'addComment', 'addDiscussionComment.html'),
(72, 'listNews', 'listDashboardNewsHelp.html'),
(73, 'editNews', 'editDashboardNewsHelp.html'),
(74, 'previewNews', 'previewDashboardNewsHelp.html'),
(75, 'addNews', 'addDashboardNewsHelp.html'),
(76, 'modifyDocumentArchiveSettings', 'modifyDocumentArchiveSettingsHelp.html'),
(77, 'addDocumentArchiveSettings', 'addDocumentArchiveSettingsHelp.html'),
(78, 'listDocFields', 'listDocumentFieldsAdmin.html'),
(79, 'editDocFieldLookups', 'editDocFieldLookups.html'),
(80, 'addMetaDataForField', 'addMetaDataForField.html'),
(81, 'editMetaDataForField', 'editMetaDataForField.html'),
(82, 'removeMetaDataFromField', 'removeMetaDataFromField.html'),
(83, 'listDocs', 'listDocumentsCheckoutHelp.html'),
(84, 'editDocCheckout', 'editDocCheckoutHelp.html'),
(85, 'listDocTypes', 'listDocTypesHelp.html'),
(86, 'editDocTypeFields', 'editDocFieldHelp.html'),
(87, 'addDocTypeFieldsLink', 'addDocTypeFieldHelp.html'),
(88, 'listGroups', 'listGroupsHelp.html'),
(89, 'editGroupUnit', 'editGroupUnitHelp.html'),
(90, 'listOrg', 'listOrgHelp.html'),
(91, 'listRole', 'listRolesHelp.html'),
(92, 'listUnits', 'listUnitHelp.html'),
(93, 'editUnitOrg', 'editUnitOrgHelp.html'),
(94, 'removeUnitFromOrg', 'removeUnitFromOrgHelp.html'),
(95, 'addUnitToOrg', 'addUnitToOrgHelp.html'),
(96, 'listUsers', 'listUsersHelp.html'),
(97, 'editUserGroups', 'editUserGroupsHelp.html'),
(98, 'listWebsites', 'listWebsitesHelp.html'),
(99, 'loginDisclaimer', 'loginDisclaimer.html'),
(100, 'pageDisclaimer', 'pageDisclaimer.html');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `help_replacement`
--


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

--
-- Dumping data for table `index_files`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `interceptor_instances`
--

INSERT INTO `interceptor_instances` (`id`, `name`, `interceptor_namespace`, `config`) VALUES
(1, 'Password Reset Interceptor', 'password.reset.login.interceptor', ''),
(2, 'Password Reset Interceptor', 'password.reset.login.interceptor', ''),
(3, 'Password Reset Interceptor', 'password.reset.login.interceptor', ''),
(4, 'Password Reset Interceptor', 'password.reset.login.interceptor', '');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `links`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `metadata_lookup`
--

INSERT INTO `metadata_lookup` (`id`, `document_field_id`, `name`, `treeorg_parent`, `disabled`, `is_stuck`) VALUES
(2, 4, 'Technical', NULL, 0, 0),
(3, 4, 'Financial', NULL, 0, 0),
(4, 4, 'Legal', NULL, 0, 0),
(5, 4, 'Administrative', NULL, 0, 0),
(6, 4, 'Miscellaneous', NULL, 0, 0),
(7, 4, 'Sales', NULL, 0, 0),
(8, 5, 'Text', NULL, 0, 0),
(9, 5, 'Image', NULL, 0, 0),
(10, 5, 'Audio', NULL, 0, 0),
(11, 5, 'Video', NULL, 0, 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `metadata_lookup_tree`
--


-- --------------------------------------------------------

--
-- Table structure for table `mime_documents`
--

CREATE TABLE IF NOT EXISTS `mime_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mime_doc` varchar(100) DEFAULT NULL,
  `icon_path` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `mime_documents`
--


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

--
-- Dumping data for table `mime_document_mapping`
--


-- --------------------------------------------------------

--
-- Table structure for table `mime_extractors`
--

CREATE TABLE IF NOT EXISTS `mime_extractors` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `mime_extractors`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `mime_types`
--

INSERT INTO `mime_types` (`id`, `filetypes`, `mimetypes`, `icon_path`, `friendly_name`, `extractor_id`, `mime_document_id`) VALUES
(1, 'ai', 'application/ai', 'image', 'Adobe Illustrator Vector Graphic', NULL, NULL),
(2, 'aif', 'audio/x-aiff', NULL, '', NULL, NULL),
(3, 'aifc', 'audio/x-aiff', NULL, '', NULL, NULL),
(4, 'aiff', 'audio/x-aiff', NULL, '', NULL, NULL),
(5, 'asc', 'text/plain', 'text', 'Plain Text', NULL, NULL),
(6, 'au', 'audio/basic', NULL, '', NULL, NULL),
(7, 'avi', 'video/x-msvideo', NULL, 'Video File', NULL, NULL),
(8, 'bcpio', 'application/x-bcpio', NULL, '', NULL, NULL),
(9, 'bin', 'application/octet-stream', NULL, 'Binary File', NULL, NULL),
(10, 'bmp', 'image/bmp', 'image', 'BMP Image', NULL, NULL),
(11, 'cdf', 'application/x-netcdf', NULL, '', NULL, NULL),
(12, 'class', 'application/octet-stream', NULL, '', NULL, NULL),
(13, 'cpio', 'application/x-cpio', NULL, '', NULL, NULL),
(14, 'cpt', 'application/mac-compactpro', NULL, '', NULL, NULL),
(15, 'csh', 'application/x-csh', NULL, '', NULL, NULL),
(16, 'css', 'text/css', NULL, '', NULL, NULL),
(17, 'dcr', 'application/x-director', NULL, '', NULL, NULL),
(18, 'dir', 'application/x-director', NULL, '', NULL, NULL),
(19, 'dms', 'application/octet-stream', NULL, '', NULL, NULL),
(20, 'doc', 'application/msword', 'word', 'Word Document', NULL, NULL),
(21, 'dvi', 'application/x-dvi', NULL, '', NULL, NULL),
(22, 'dxr', 'application/x-director', NULL, '', NULL, NULL),
(23, 'eps', 'application/eps', 'image', 'Encapsulated Postscript', NULL, NULL),
(24, 'etx', 'text/x-setext', NULL, '', NULL, NULL),
(25, 'exe', 'application/octet-stream', NULL, '', NULL, NULL),
(26, 'ez', 'application/andrew-inset', NULL, '', NULL, NULL),
(27, 'gif', 'image/gif', 'image', 'GIF Image', NULL, NULL),
(28, 'gtar', 'application/x-gtar', 'compressed', '', NULL, NULL),
(29, 'hdf', 'application/x-hdf', NULL, '', NULL, NULL),
(30, 'hqx', 'application/mac-binhex40', NULL, '', NULL, NULL),
(31, 'htm', 'text/html', 'html', 'HTML Webpage', NULL, NULL),
(32, 'html', 'text/html', 'html', 'HTML Webpage', NULL, NULL),
(33, 'ice', 'x-conference/x-cooltalk', NULL, '', NULL, NULL),
(34, 'ief', 'image/ief', 'image', '', NULL, NULL),
(35, 'iges', 'model/iges', NULL, '', NULL, NULL),
(36, 'igs', 'model/iges', NULL, '', NULL, NULL),
(37, 'jpe', 'image/jpeg', 'image', 'JPEG Image', NULL, NULL),
(38, 'jpeg', 'image/jpeg', 'image', 'JPEG Image', NULL, NULL),
(39, 'jpg', 'image/jpeg', 'image', 'JPEG Image', NULL, NULL),
(40, 'js', 'application/x-javascript', 'html', '', NULL, NULL),
(41, 'kar', 'audio/midi', NULL, '', NULL, NULL),
(42, 'latex', 'application/x-latex', NULL, '', NULL, NULL),
(43, 'lha', 'application/octet-stream', NULL, '', NULL, NULL),
(44, 'lzh', 'application/octet-stream', NULL, '', NULL, NULL),
(45, 'man', 'application/x-troff-man', NULL, '', NULL, NULL),
(46, 'mdb', 'application/access', 'database', 'Access Database', NULL, NULL),
(47, 'mdf', 'application/access', 'database', 'Access Database', NULL, NULL),
(48, 'me', 'application/x-troff-me', NULL, '', NULL, NULL),
(49, 'mesh', 'model/mesh', NULL, '', NULL, NULL),
(50, 'mid', 'audio/midi', NULL, '', NULL, NULL),
(51, 'midi', 'audio/midi', NULL, '', NULL, NULL),
(52, 'mif', 'application/vnd.mif', NULL, '', NULL, NULL),
(53, 'mov', 'video/quicktime', NULL, 'Video File', NULL, NULL),
(54, 'movie', 'video/x-sgi-movie', NULL, 'Video File', NULL, NULL),
(55, 'mp2', 'audio/mpeg', NULL, '', NULL, NULL),
(56, 'mp3', 'audio/mpeg', NULL, '', NULL, NULL),
(57, 'mpe', 'video/mpeg', NULL, 'Video File', NULL, NULL),
(58, 'mpeg', 'video/mpeg', NULL, 'Video File', NULL, NULL),
(59, 'mpg', 'video/mpeg', NULL, 'Video File', NULL, NULL),
(60, 'mpga', 'audio/mpeg', NULL, '', NULL, NULL),
(61, 'mpp', 'application/vnd.ms-project', 'office', '', NULL, NULL),
(62, 'ms', 'application/x-troff-ms', NULL, '', NULL, NULL),
(63, 'msh', 'model/mesh', NULL, '', NULL, NULL),
(64, 'nc', 'application/x-netcdf', NULL, '', NULL, NULL),
(65, 'oda', 'application/oda', NULL, '', NULL, NULL),
(66, 'pbm', 'image/x-portable-bitmap', 'image', '', NULL, NULL),
(67, 'pdb', 'chemical/x-pdb', NULL, '', NULL, NULL),
(68, 'pdf', 'application/pdf', 'pdf', 'Acrobat PDF', NULL, NULL),
(69, 'pgm', 'image/x-portable-graymap', 'image', '', NULL, NULL),
(70, 'pgn', 'application/x-chess-pgn', NULL, '', NULL, NULL),
(71, 'png', 'image/png', 'image', 'PNG Image', NULL, NULL),
(72, 'pnm', 'image/x-portable-anymap', 'image', '', NULL, NULL),
(73, 'ppm', 'image/x-portable-pixmap', 'image', '', NULL, NULL),
(74, 'ppt', 'application/vnd.ms-powerpoint', 'office', 'Powerpoint Presentation', NULL, NULL),
(75, 'ps', 'application/postscript', 'pdf', 'Postscript Document', NULL, NULL),
(76, 'qt', 'video/quicktime', NULL, 'Video File', NULL, NULL),
(77, 'ra', 'audio/x-realaudio', NULL, '', NULL, NULL),
(78, 'ram', 'audio/x-pn-realaudio', NULL, '', NULL, NULL),
(79, 'ras', 'image/x-cmu-raster', 'image', '', NULL, NULL),
(80, 'rgb', 'image/x-rgb', 'image', '', NULL, NULL),
(81, 'rm', 'audio/x-pn-realaudio', NULL, '', NULL, NULL),
(82, 'roff', 'application/x-troff', NULL, '', NULL, NULL),
(83, 'rpm', 'audio/x-pn-realaudio-plugin', NULL, '', NULL, NULL),
(84, 'rtf', 'text/rtf', NULL, '', NULL, NULL),
(85, 'rtx', 'text/richtext', NULL, '', NULL, NULL),
(86, 'sgm', 'text/sgml', NULL, '', NULL, NULL),
(87, 'sgml', 'text/sgml', NULL, '', NULL, NULL),
(88, 'sh', 'application/x-sh', NULL, '', NULL, NULL),
(89, 'shar', 'application/x-shar', NULL, '', NULL, NULL),
(90, 'silo', 'model/mesh', NULL, '', NULL, NULL),
(91, 'sit', 'application/x-stuffit', NULL, '', NULL, NULL),
(92, 'skd', 'application/x-koan', NULL, '', NULL, NULL),
(93, 'skm', 'application/x-koan', NULL, '', NULL, NULL),
(94, 'skp', 'application/x-koan', NULL, '', NULL, NULL),
(95, 'skt', 'application/x-koan', NULL, '', NULL, NULL),
(96, 'smi', 'application/smil', NULL, '', NULL, NULL),
(97, 'smil', 'application/smil', NULL, '', NULL, NULL),
(98, 'snd', 'audio/basic', NULL, '', NULL, NULL),
(99, 'spl', 'application/x-futuresplash', NULL, '', NULL, NULL),
(100, 'src', 'application/x-wais-source', NULL, '', NULL, NULL),
(101, 'sv4cpio', 'application/x-sv4cpio', NULL, '', NULL, NULL),
(102, 'sv4crc', 'application/x-sv4crc', NULL, '', NULL, NULL),
(103, 'swf', 'application/x-shockwave-flash', NULL, '', NULL, NULL),
(104, 't', 'application/x-troff', NULL, '', NULL, NULL),
(105, 'tar', 'application/x-tar', 'compressed', 'Tar or Compressed Tar File', NULL, NULL),
(106, 'tcl', 'application/x-tcl', NULL, '', NULL, NULL),
(107, 'tex', 'application/x-tex', NULL, '', NULL, NULL),
(108, 'texi', 'application/x-texinfo', NULL, '', NULL, NULL),
(109, 'texinfo', 'application/x-texinfo', NULL, '', NULL, NULL),
(110, 'tif', 'image/tiff', 'image', 'TIFF Image', NULL, NULL),
(111, 'tiff', 'image/tiff', 'image', 'TIFF Image', NULL, NULL),
(112, 'tr', 'application/x-troff', NULL, '', NULL, NULL),
(113, 'tsv', 'text/tab-separated-values', NULL, '', NULL, NULL),
(114, 'txt', 'text/plain', 'text', 'Plain Text', NULL, NULL),
(115, 'ustar', 'application/x-ustar', NULL, '', NULL, NULL),
(116, 'vcd', 'application/x-cdlink', NULL, '', NULL, NULL),
(117, 'vrml', 'model/vrml', NULL, '', NULL, NULL),
(118, 'vsd', 'application/vnd.visio', 'office', '', NULL, NULL),
(119, 'wav', 'audio/x-wav', NULL, '', NULL, NULL),
(120, 'wrl', 'model/vrml', NULL, '', NULL, NULL),
(121, 'xbm', 'image/x-xbitmap', 'image', '', NULL, NULL),
(122, 'xls', 'application/vnd.ms-excel', 'excel', 'Excel Spreadsheet', NULL, NULL),
(123, 'xml', 'text/xml', NULL, '', NULL, NULL),
(124, 'xpm', 'image/x-xpixmap', 'image', '', NULL, NULL),
(125, 'xwd', 'image/x-xwindowdump', 'image', '', NULL, NULL),
(126, 'xyz', 'chemical/x-pdb', NULL, '', NULL, NULL),
(127, 'zip', 'application/zip', 'compressed', 'ZIP Compressed File', NULL, NULL),
(128, 'gz', 'application/x-gzip', 'compressed', 'GZIP Compressed File', NULL, NULL),
(129, 'tgz', 'application/x-gzip', 'compressed', 'Tar or Compressed Tar File', NULL, NULL),
(130, 'sxw', 'application/vnd.sun.xml.writer', 'openoffice', 'OpenOffice.org Writer Document', NULL, NULL),
(131, 'stw', 'application/vnd.sun.xml.writer.template', 'openoffice', 'OpenOffice.org File', NULL, NULL),
(132, 'sxc', 'application/vnd.sun.xml.calc', 'openoffice', 'OpenOffice.org Spreadsheet', NULL, NULL),
(133, 'stc', 'application/vnd.sun.xml.calc.template', 'openoffice', 'OpenOffice.org File', NULL, NULL),
(134, 'sxd', 'application/vnd.sun.xml.draw', 'openoffice', 'OpenOffice.org File', NULL, NULL),
(135, 'std', 'application/vnd.sun.xml.draw.template', 'openoffice', 'OpenOffice.org File', NULL, NULL),
(136, 'sxi', 'application/vnd.sun.xml.impress', 'openoffice', 'OpenOffice.org Presentation', NULL, NULL),
(137, 'sti', 'application/vnd.sun.xml.impress.template', 'openoffice', 'OpenOffice.org File', NULL, NULL),
(138, 'sxg', 'application/vnd.sun.xml.writer.global', 'openoffice', 'OpenOffice.org File', NULL, NULL),
(139, 'sxm', 'application/vnd.sun.xml.math', 'openoffice', 'OpenOffice.org File', NULL, NULL),
(140, 'xlt', 'application/vnd.ms-excel', 'excel', 'Excel Template', NULL, NULL),
(141, 'dot', 'application/msword', 'word', 'Word Template', NULL, NULL),
(142, 'bz2', 'application/x-bzip2', 'compressed', 'BZIP2 Compressed File', NULL, NULL),
(143, 'diff', 'text/plain', 'text', 'Source Diff File', NULL, NULL),
(144, 'patch', 'text/plain', 'text', 'Patch File', NULL, NULL),
(145, 'odt', 'application/vnd.oasis.opendocument.text', 'opendocument', 'OpenDocument Text', NULL, NULL),
(146, 'ott', 'application/vnd.oasis.opendocument.text-template', 'opendocument', 'OpenDocument Text Template', NULL, NULL),
(147, 'oth', 'application/vnd.oasis.opendocument.text-web', 'opendocument', 'HTML Document Template', NULL, NULL),
(148, 'odm', 'application/vnd.oasis.opendocument.text-master', 'opendocument', 'OpenDocument Master Document', NULL, NULL),
(149, 'odg', 'application/vnd.oasis.opendocument.graphics', 'opendocument', 'OpenDocument Drawing', NULL, NULL),
(150, 'otg', 'application/vnd.oasis.opendocument.graphics-template', 'opendocument', 'OpenDocument Drawing Template', NULL, NULL),
(151, 'odp', 'application/vnd.oasis.opendocument.presentation', 'opendocument', 'OpenDocument Presentation', NULL, NULL),
(152, 'otp', 'application/vnd.oasis.opendocument.presentation-template', 'opendocument', 'OpenDocument Presentation Template', NULL, NULL),
(153, 'ods', 'application/vnd.oasis.opendocument.spreadsheet', 'opendocument', 'OpenDocument Spreadsheet', NULL, NULL),
(154, 'ots', 'application/vnd.oasis.opendocument.spreadsheet-template', 'opendocument', 'OpenDocument Spreadsheet Template', NULL, NULL),
(155, 'odc', 'application/vnd.oasis.opendocument.chart', 'opendocument', 'OpenDocument Chart', NULL, NULL),
(156, 'odf', 'application/vnd.oasis.opendocument.formula', 'opendocument', 'OpenDocument Formula', NULL, NULL),
(157, 'odb', 'application/vnd.oasis.opendocument.database', 'opendocument', 'OpenDocument Database', NULL, NULL),
(158, 'odi', 'application/vnd.oasis.opendocument.image', 'opendocument', 'OpenDocument Image', NULL, NULL),
(159, 'zip', 'application/x-zip', 'compressed', 'ZIP Compressed File', NULL, NULL),
(160, 'csv', 'text/csv', 'excel', 'Comma delimited spreadsheet', NULL, NULL),
(161, 'msi', 'application/x-msi', 'compressed', 'MSI Installer file', NULL, NULL),
(162, 'pps', 'application/vnd.ms-powerpoint', 'office', 'Powerpoint Presentation', NULL, NULL),
(163, 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'word', 'Word Document', NULL, NULL),
(164, 'dotx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'word', 'Word Document', NULL, NULL),
(165, 'potx', 'application/vnd.openxmlformats-officedocument.presentationml.template', 'office', 'Powerpoint Presentation', NULL, NULL),
(166, 'ppsx', 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'office', 'Powerpoint Presentation', NULL, NULL),
(167, 'pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'office', 'Powerpoint Presentation', NULL, NULL),
(168, 'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'excel', 'Excel Spreadsheet', NULL, NULL),
(169, 'xltx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'excel', 'Excel Spreadsheet', NULL, NULL),
(170, 'msg', 'application/vnd.ms-outlook', 'office', 'Outlook Item', NULL, NULL),
(171, 'db', 'application/db', '', 'Misc DB file', NULL, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `news`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `notifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `organisations_lookup`
--

CREATE TABLE IF NOT EXISTS `organisations_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `organisations_lookup`
--

INSERT INTO `organisations_lookup` (`id`, `name`) VALUES
(1, 'Default Organisation');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `human_name`, `built_in`) VALUES
(1, 'ktcore.permissions.read', 'Read', 1),
(2, 'ktcore.permissions.write', 'Write', 1),
(3, 'ktcore.permissions.addFolder', 'Add Folder', 1),
(4, 'ktcore.permissions.security', 'Manage security', 1),
(5, 'ktcore.permissions.delete', 'Delete', 1),
(6, 'ktcore.permissions.workflow', 'Manage workflow', 1),
(7, 'ktcore.permissions.folder_details', 'Folder Details', 1),
(8, 'ktcore.permissions.folder_rename', 'Rename Folder', 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `permission_assignments`
--

INSERT INTO `permission_assignments` (`id`, `permission_id`, `permission_object_id`, `permission_descriptor_id`) VALUES
(1, 1, 1, 2),
(2, 2, 1, 2),
(3, 3, 1, 2),
(4, 4, 1, 2),
(5, 5, 1, 2),
(6, 6, 1, 2),
(7, 7, 1, 2),
(8, 8, 1, 2),
(9, 1, 2, 3),
(10, 2, 2, 3),
(11, 3, 2, 3),
(12, 4, 2, 2),
(13, 5, 2, 2),
(14, 6, 2, 2),
(15, 7, 2, 3),
(16, 8, 2, 2),
(17, 1, 3, 3),
(18, 2, 3, 3),
(19, 3, 3, 3),
(20, 4, 3, 3),
(21, 5, 3, 3),
(22, 6, 3, 3),
(23, 7, 3, 3),
(24, 8, 3, 3);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `permission_descriptors`
--

INSERT INTO `permission_descriptors` (`id`, `descriptor`, `descriptor_text`) VALUES
(1, 'd41d8cd98f00b204e9800998ecf8427e', ''),
(2, 'a689e7c4dc953de8d93b1ed4843b2dfe', 'group(1)'),
(3, '426b9d5f4837e3407e43f96722cbe308', 'group(1)role(5)'),
(4, '69956554f671b2f1819ff895730ceff9', 'user(1)'),
(5, 'bca11de862fdb4a335a3001ea80d9b61', 'group(1)user(1)');

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

--
-- Dumping data for table `permission_descriptor_groups`
--

INSERT INTO `permission_descriptor_groups` (`descriptor_id`, `group_id`) VALUES
(2, 1),
(3, 1),
(5, 1);

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

--
-- Dumping data for table `permission_descriptor_roles`
--

INSERT INTO `permission_descriptor_roles` (`descriptor_id`, `role_id`) VALUES
(3, 5);

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

--
-- Dumping data for table `permission_descriptor_users`
--

INSERT INTO `permission_descriptor_users` (`descriptor_id`, `user_id`) VALUES
(4, 1),
(5, 1);

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

--
-- Dumping data for table `permission_dynamic_assignments`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `permission_dynamic_conditions`
--


-- --------------------------------------------------------

--
-- Table structure for table `permission_lookups`
--

CREATE TABLE IF NOT EXISTS `permission_lookups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `permission_lookups`
--

INSERT INTO `permission_lookups` (`id`) VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9),
(10);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `permission_lookup_assignments`
--

INSERT INTO `permission_lookup_assignments` (`id`, `permission_id`, `permission_lookup_id`, `permission_descriptor_id`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(3, 3, 1, 1),
(4, 1, 2, 2),
(5, 2, 2, 2),
(6, 3, 2, 2),
(7, 1, 3, 2),
(8, 2, 3, 2),
(9, 3, 3, 2),
(10, 4, 3, 2),
(11, 5, 3, 2),
(12, 1, 4, 2),
(13, 2, 4, 2),
(14, 3, 4, 2),
(15, 4, 4, 2),
(16, 5, 4, 2),
(17, 6, 4, 2),
(18, 1, 5, 2),
(19, 2, 5, 2),
(20, 3, 5, 2),
(21, 4, 5, 2),
(22, 5, 5, 2),
(23, 6, 5, 2),
(24, 7, 5, 2),
(25, 1, 6, 2),
(26, 2, 6, 2),
(27, 3, 6, 2),
(28, 4, 6, 2),
(29, 5, 6, 2),
(30, 6, 6, 2),
(31, 7, 6, 2),
(32, 8, 6, 2),
(33, 1, 7, 3),
(34, 2, 7, 3),
(35, 3, 7, 3),
(36, 4, 7, 2),
(37, 5, 7, 2),
(38, 6, 7, 2),
(39, 7, 7, 3),
(40, 8, 7, 2),
(41, 1, 8, 5),
(42, 2, 8, 5),
(43, 3, 8, 5),
(44, 4, 8, 2),
(45, 5, 8, 2),
(46, 6, 8, 2),
(47, 7, 8, 5),
(48, 8, 8, 2),
(49, 1, 9, 3),
(50, 2, 9, 3),
(51, 3, 9, 3),
(52, 4, 9, 3),
(53, 5, 9, 3),
(54, 6, 9, 3),
(55, 7, 9, 3),
(56, 8, 9, 3),
(57, 1, 10, 5),
(58, 2, 10, 5),
(59, 3, 10, 5),
(60, 4, 10, 5),
(61, 5, 10, 5),
(62, 6, 10, 5),
(63, 7, 10, 5),
(64, 8, 10, 5);

-- --------------------------------------------------------

--
-- Table structure for table `permission_objects`
--

CREATE TABLE IF NOT EXISTS `permission_objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `permission_objects`
--

INSERT INTO `permission_objects` (`id`) VALUES
(1),
(2),
(3);

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
  `list_admin` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `plugins`
--

INSERT INTO `plugins` (`id`, `namespace`, `path`, `version`, `disabled`, `data`, `unavailable`, `friendly_name`, `orderby`, `list_admin`) VALUES
(1, 'ktcore.tagcloud.plugin', 'plugins/tagcloud/TagCloudPlugin.php', 1, 0, NULL, 0, 'Tag Cloud Plugin', 0, 1),
(2, 'ktcore.rss.plugin', 'plugins/rssplugin/RSSPlugin.php', 0, 0, NULL, 0, 'RSS Plugin', 0, 1),
(3, 'ktcore.language.plugin', 'plugins/ktcore/KTCoreLanguagePlugin.php', 0, 0, NULL, 0, 'Core Language Support', -75, 0),
(4, 'ktcore.plugin', 'plugins/ktcore/KTCorePlugin.php', 0, 0, NULL, 0, 'Core Application Functionality', -25, 0),
(5, 'ktstandard.ldapauthentication.plugin', 'plugins/ktstandard/KTLDAPAuthenticationPlugin.php', 0, 0, NULL, 0, 'LDAP Authentication Plugin', 0, 1),
(6, 'ktstandard.pdf.plugin', 'plugins/ktstandard/PDFGeneratorPlugin.php', 0, 0, NULL, 0, 'PDF Generator Plugin', 0, 1),
(7, 'ktstandard.bulkexport.plugin', 'plugins/ktstandard/KTBulkExportPlugin.php', 0, 0, NULL, 0, 'Bulk Export Plugin', 0, 1),
(8, 'ktstandard.immutableaction.plugin', 'plugins/ktstandard/ImmutableActionPlugin.php', 0, 0, NULL, 0, 'Immutable action plugin', 0, 1),
(9, 'ktstandard.subscriptions.plugin', 'plugins/ktstandard/KTSubscriptions.php', 0, 0, NULL, 0, 'Subscription Plugin', 0, 1),
(10, 'ktstandard.discussion.plugin', 'plugins/ktstandard/KTDiscussion.php', 0, 0, NULL, 0, 'Document Discussions Plugin', 0, 1),
(11, 'ktstandard.email.plugin', 'plugins/ktstandard/KTEmail.php', 0, 0, NULL, 0, 'Email Plugin', 0, 1),
(12, 'ktstandard.indexer.plugin', 'plugins/ktstandard/KTIndexer.php', 0, 0, NULL, 0, 'Full-text Content Indexing', 0, 1),
(13, 'ktstandard.documentlinks.plugin', 'plugins/ktstandard/KTDocumentLinks.php', 0, 0, NULL, 0, 'Inter-document linking', 0, 1),
(14, 'ktstandard.workflowassociation.plugin', 'plugins/ktstandard/KTWorkflowAssociation.php', 0, 0, NULL, 0, 'Workflow Association Plugin', 0, 1),
(15, 'ktstandard.workflowassociation.documenttype.plugin', 'plugins/ktstandard/workflow/TypeAssociator.php', 0, 0, NULL, 0, 'Workflow allocation by document type', 0, 1),
(16, 'ktstandard.workflowassociation.folder.plugin', 'plugins/ktstandard/workflow/FolderAssociator.php', 0, 0, NULL, 0, 'Workflow allocation by location', 0, 1),
(17, 'ktstandard.disclaimers.plugin', 'plugins/ktstandard/KTDisclaimers.php', 0, 0, NULL, 0, 'Disclaimers Plugin', 0, 1),
(18, 'nbm.browseable.plugin', 'plugins/browseabledashlet/BrowseableDashletPlugin.php', 0, 0, NULL, 0, 'Orphaned Folders Plugin', 0, 1),
(19, 'ktstandard.ktwebdavdashlet.plugin', 'plugins/ktstandard/KTWebDAVDashletPlugin.php', 0, 0, NULL, 0, 'WebDAV Dashlet Plugin', 0, 1),
(20, 'ktcore.housekeeper.plugin', 'plugins/housekeeper/HouseKeeperPlugin.php', 0, 0, NULL, 0, 'Housekeeper', 0, 1),
(21, 'ktstandard.preview.plugin', 'plugins/ktstandard/documentpreview/documentPreviewPlugin.php', 0, 0, NULL, 0, 'Property Preview Plugin', 0, 1),
(22, 'ktlive.mydropdocuments.plugin', 'plugins/MyDropDocumentsPlugin/MyDropDocumentsPlugin.php', 0, 0, NULL, 0, 'Drop Documents Plugin', 0, 1),
(23, 'ktcore.i18.de_DE.plugin', 'plugins/i18n/german/GermanPlugin.php', 0, 0, NULL, 0, 'German translation plugin', -50, 1),
(24, 'ktcore.i18.ja_JA.plugin', 'plugins/commercial-plugins/i18n/japanese/JapanesePlugin.php', 0, 0, NULL, 0, 'Commercial Japanese translation plugin', -50, 1),
(25, 'ktcore.i18.it_IT.plugin', 'plugins/i18n/italian/ItalianPlugin.php', 0, 0, NULL, 0, 'Italian translation plugin', -50, 1),
(26, 'ktcore.i18.fr_FR.plugin', 'plugins/i18n/french/FrenchPlugin.php', 0, 0, NULL, 0, 'French translation', -50, 1),
(27, 'ktdms.wintools', 'plugins/commercial-plugins/wintools/BaobabPlugin.php', 2, 0, NULL, 0, 'Windows Tools:  Key Management', -20, 1),
(28, 'password.reset.plugin', 'plugins/passwordResetPlugin/passwordResetPlugin.php', 0, 1, NULL, 0, 'Password Reset Plugin', 0, 1),
(29, 'pdf.converter.processor.plugin', 'plugins/pdfConverter/pdfConverterPlugin.php', 0, 0, NULL, 0, 'Document PDF Converter', 0, 1),
(30, 'office.addin.plugin', 'plugins/commercial-plugins/officeaddin/officeaddinPlugin.php', 0, 0, NULL, 0, 'Office Add-In Plugin', 0, 1),
(31, 'document.alerts.plugin', 'plugins/commercial-plugins/alerts/alertPlugin.php', 1, 0, NULL, 0, 'Document Alerts Plugin', 0, 1),
(32, 'client.tools.plugin', 'plugins/commercial-plugins/clienttools/clientToolsPlugin.php', 0, 0, NULL, 0, 'Client Tools Plugin', 0, 1),
(33, 'custom-numbering.plugin', 'plugins/commercial-plugins/custom-numbering/CustomNumberingPlugin.php', 0, 0, NULL, 1, 'Custom Numbering', 0, 1),
(34, 'guid.inserter.plugin', 'plugins/commercial-plugins/guidInserter/guidInserterPlugin.php', 0, 1, NULL, 0, 'Document GUID Inserter (Experimental)', 0, 1),
(35, 'ktextra.conditionalmetadata.plugin', 'plugins/commercial-plugins/conditional-metadata/ConditionalMetadataPlugin.php', 0, 0, NULL, 0, 'Conditional Metadata Plugin', 0, 1),
(36, 'shortcuts.plugin', 'plugins/commercial-plugins/shortcuts/ShortcutsPlugin.php', 0, 0, NULL, 0, 'Shortcuts', 0, 1),
(37, 'electronic.signatures.plugin', 'plugins/commercial-plugins/electronic-signatures/KTElectronicSignaturesPlugin.php', 0, 0, NULL, 0, 'Electronic Signatures', 0, 1),
(38, 'document.comparison.plugin', 'plugins/commercial-plugins/documentcomparison/DocumentComparisonPlugin.php', 0, 0, NULL, 1, 'Document Comparison Plugin', 0, 1),
(39, 'ktnetwork.inlineview.plugin', 'plugins/commercial-plugins/network/inlineview/InlineViewPlugin.php', 0, 0, NULL, 0, 'Inline View of Documents', 0, 1),
(40, 'ktnetwork.GoToDocumentId.plugin', 'plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php', 0, 0, NULL, 0, 'Document Jump Dashlet', 0, 1),
(41, 'ktprofessional.reporting.plugin', 'plugins/commercial-plugins/professional-reporting/ProfessionalReportingPlugin.php', 0, 0, NULL, 0, 'Professional Reporting', 0, 1),
(42, 'ktnetwork.TopDownloads.plugin', 'plugins/commercial-plugins/network/topdownloads/TopDownloadsPlugin.php', 0, 0, NULL, 0, 'Top Downloads for the last Week', 0, 1),
(43, 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'plugins/commercial-plugins/network/extendedtransactioninfo/ExtendedTransactionInfoPlugin.php', 0, 0, NULL, 0, 'Extended Transaction Information', 0, 1),
(44, 'bd.Quicklinks.plugin', 'plugins/commercial-plugins/network/quicklinks/QuicklinksPlugin.php', 2, 0, NULL, 0, 'Quicklinks Plugin', 0, 1),
(45, 'brad.UserHistory.plugin', 'plugins/commercial-plugins/network/userhistory/UserHistoryPlugin.php', 0, 0, NULL, 0, 'User History', 0, 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `plugin_rss`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `quicklinks`
--


-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(-4, 'Authenticated Users'),
(4, 'Creator'),
(-3, 'Everyone'),
(-2, 'Owner'),
(2, 'Publisher'),
(3, 'Reviewer'),
(5, 'WorkSpaceOwner');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `role_allocations`
--

INSERT INTO `role_allocations` (`id`, `folder_id`, `role_id`, `permission_descriptor_id`) VALUES
(1, 2, 5, 4),
(2, 3, 5, 4);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `saved_searches`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `scheduler_tasks`
--

INSERT INTO `scheduler_tasks` (`id`, `task`, `script_url`, `script_params`, `is_complete`, `frequency`, `run_time`, `previous_run_time`, `run_duration`, `status`) VALUES
(1, 'Document Processor', 'search2/bin/cronDocumentProcessor.php', '', 0, '1min', '2007-10-01 00:00:00', NULL, 0, 'system'),
(2, 'Index Migration', 'search2/bin/cronMigration.php', '', 0, '5mins', '2007-10-01 00:00:00', NULL, 0, 'system'),
(3, 'Index Optimization', 'search2/bin/cronOptimize.php', '', 0, 'weekly', '2007-10-01 00:00:00', NULL, 0, 'system'),
(4, 'Periodic Document Expunge', 'bin/expungeall.php', '', 0, 'weekly', '2007-10-01 00:00:00', NULL, 0, 'disabled'),
(5, 'Database Maintenance', 'bin/dbmaint.php', 'optimize', 0, 'monthly', '2007-10-01 00:00:00', NULL, 0, 'disabled'),
(6, 'OpenOffice test', 'bin/checkopenoffice.php', '', 0, '1min', '2007-10-01 00:00:00', NULL, 0, 'enabled'),
(7, 'Cleanup Temporary Directory', 'search2/bin/cronCleanup.php', '', 0, '1min', '2007-10-01 00:00:00', NULL, 0, 'enabled'),
(8, 'Disk Usage and Folder Utilisation Statistics', 'plugins/housekeeper/bin/UpdateStats.php', '', 0, '5mins', '2007-10-01 00:00:00', NULL, 0, 'enabled'),
(9, 'Refresh Index Statistics', 'search2/bin/cronIndexStats.php', '', 0, '1min', '2007-10-01 00:00:00', NULL, 0, 'enabled'),
(10, 'Refresh Resource Dependancies', 'search2/bin/cronResources.php', '', 0, '1min', '2007-10-01 00:00:00', NULL, 0, 'enabled'),
(11, 'Bulk Download Queue', 'bin/ajaxtasks/downloadTask.php', '', 0, '1min', '2007-10-01 00:00:00', NULL, 0, 'system'),
(12, 'Document Alerts', 'plugins/commercial-plugins/alerts/alertTask.php', '', 0, 'daily', '2009-08-26 06:00:00', '2009-08-25 06:00:00', 0, 'enabled');

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

--
-- Dumping data for table `search_document_user_link`
--


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

--
-- Dumping data for table `search_ranking`
--

INSERT INTO `search_ranking` (`groupname`, `itemname`, `ranking`, `type`) VALUES
('Discussion', '', 150, 'S'),
('documents', 'checked_out_user_id', 1, 'T'),
('documents', 'created', 1, 'T'),
('documents', 'creator_id', 1, 'T'),
('documents', 'id', 1, 'T'),
('documents', 'immutable', 1, 'T'),
('documents', 'is_checked_out', 1, 'T'),
('documents', 'modified', 1, 'T'),
('documents', 'modified_user_id', 1, 'T'),
('documents', 'title', 300, 'T'),
('DocumentText', '', 100, 'S'),
('document_content_version', 'filename', 10, 'T'),
('document_content_version', 'filesize', 1, 'T'),
('document_fields_link', 'value', 1, 'T'),
('document_metadata_version', 'document_type_id', 1, 'T'),
('document_metadata_version', 'name', 300, 'T'),
('document_metadata_version', 'workflow_id', 1, 'T'),
('document_metadata_version', 'workflow_state_id', 1, 'T'),
('tag_words', 'tag', 1, 'T');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `search_saved`
--


-- --------------------------------------------------------

--
-- Table structure for table `search_saved_events`
--

CREATE TABLE IF NOT EXISTS `search_saved_events` (
  `document_id` int(11) NOT NULL,
  PRIMARY KEY (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `search_saved_events`
--


-- --------------------------------------------------------

--
-- Table structure for table `status_lookup`
--

CREATE TABLE IF NOT EXISTS `status_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `status_lookup`
--

INSERT INTO `status_lookup` (`id`, `name`) VALUES
(1, 'Live'),
(2, 'Published'),
(3, 'Deleted'),
(4, 'Archived'),
(5, 'Incomplete'),
(6, 'Version Deleted');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `value`) VALUES
(1, 'lastIndexUpdate', '0'),
(2, 'knowledgeTreeVersion', '3.7.0\r\n'),
(3, 'databaseVersion', '3.7.0'),
(4, 'server_name', '127.0.0.1'),
(5, 'dashboard-state-1', '{"left":[{"id":"KTInfoDashlet","state":0},{"id":"schedulerDashlet","state":0},{"id":"RSSDashlet","state":0},{"id":"MyDropDocumentsDashlet","state":0}],"right":[{"id":"KTMailServerDashlet","state":0},{"id":"KTWebDAVDashlet","state":0},{"id":"RSSDedicatedDashlet","state":0}]}');

-- --------------------------------------------------------

--
-- Table structure for table `tag_words`
--

CREATE TABLE IF NOT EXISTS `tag_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `tag_words`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `time_period`
--


-- --------------------------------------------------------

--
-- Table structure for table `time_unit_lookup`
--

CREATE TABLE IF NOT EXISTS `time_unit_lookup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `time_unit_lookup`
--

INSERT INTO `time_unit_lookup` (`id`, `name`) VALUES
(1, 'Years'),
(2, 'Months'),
(3, 'Days');

-- --------------------------------------------------------

--
-- Table structure for table `trigger_selection`
--

CREATE TABLE IF NOT EXISTS `trigger_selection` (
  `event_ns` varchar(255) NOT NULL DEFAULT '',
  `selection_ns` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`event_ns`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `trigger_selection`
--


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

--
-- Dumping data for table `type_workflow_map`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `units_lookup`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `units_organisations_link`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `upgrades`
--

INSERT INTO `upgrades` (`id`, `descriptor`, `description`, `date_performed`, `result`, `parent`) VALUES
(1, 'sql*2.0.6*0*2.0.6/create_upgrade_table.sql', 'Database upgrade to version 2.0.6: Create upgrade table', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6'),
(2, 'upgrade*2.0.6*0*upgrade2.0.6', 'Upgrade from version 2.0.2 to 2.0.6', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6'),
(3, 'func*2.0.6*0*addTemplateMimeTypes', 'Add MIME types for Excel and Word templates', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6'),
(4, 'sql*2.0.6*0*2.0.6/add_email_attachment_transaction_type.sql', 'Database upgrade to version 2.0.6: Add email attachment transaction type', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6'),
(5, 'sql*2.0.6*0*2.0.6/create_link_type_table.sql', 'Database upgrade to version 2.0.6: Create link type table', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6'),
(6, 'sql*2.0.6*1*2.0.6/1-update_database_version.sql', 'Database upgrade to version 2.0.6: Update database version', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6'),
(7, 'upgrade*2.0.7*0*upgrade2.0.7', 'Upgrade from version 2.0.7 to 2.0.7', '2005-07-21 22:35:15', 1, 'upgrade*2.0.7*0*upgrade2.0.7'),
(8, 'sql*2.0.7*0*2.0.7/document_link_update.sql', 'Database upgrade to version 2.0.7: Document link update', '2005-07-21 22:35:16', 1, 'upgrade*2.0.7*0*upgrade2.0.7'),
(9, 'sql*2.0.8*0*2.0.8/nestedgroups.sql', 'Database upgrade to version 2.0.8: Nestedgroups', '2005-08-02 16:02:06', 1, 'upgrade*2.0.8*0*upgrade2.0.8'),
(10, 'sql*2.0.8*0*2.0.8/help_replacement.sql', 'Database upgrade to version 2.0.8: Help replacement', '2005-08-02 16:02:06', 1, 'upgrade*2.0.8*0*upgrade2.0.8'),
(11, 'upgrade*2.0.8*0*upgrade2.0.8', 'Upgrade from version 2.0.7 to 2.0.8', '2005-08-02 16:02:06', 1, 'upgrade*2.0.8*0*upgrade2.0.8'),
(12, 'sql*2.0.8*0*2.0.8/permissions.sql', 'Database upgrade to version 2.0.8: Permissions', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8'),
(13, 'func*2.0.8*1*setPermissionObject', 'Set the permission object in charge of a document or folder', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8'),
(14, 'sql*2.0.8*1*2.0.8/1-metadata_versions.sql', 'Database upgrade to version 2.0.8: Metadata versions', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8'),
(15, 'sql*2.0.8*2*2.0.8/2-permissions.sql', 'Database upgrade to version 2.0.8: Permissions', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8'),
(16, 'sql*2.0.9*0*2.0.9/storagemanager.sql', '', '0000-00-00 00:00:00', 1, NULL),
(17, 'sql*2.0.9*0*2.0.9/metadata_tree.sql', '', '0000-00-00 00:00:00', 1, NULL),
(18, 'sql*2.0.9*0*2.0.9/document_incomplete.sql', '', '0000-00-00 00:00:00', 1, NULL),
(20, 'upgrade*2.99.1*0*upgrade2.99.1', 'Upgrade from version 2.0.8 to 2.99.1', '2005-10-07 14:26:15', 1, 'upgrade*2.99.1*0*upgrade2.99.1'),
(21, 'sql*2.99.1*0*2.99.1/workflow.sql', 'Database upgrade to version 2.99.1: Workflow', '2005-10-07 14:26:15', 1, 'upgrade*2.99.1*0*upgrade2.99.1'),
(22, 'sql*2.99.1*0*2.99.1/fieldsets.sql', 'Database upgrade to version 2.99.1: Fieldsets', '2005-10-07 14:26:16', 1, 'upgrade*2.99.1*0*upgrade2.99.1'),
(23, 'func*2.99.1*1*createFieldSets', 'Create a fieldset for each field without one', '2005-10-07 14:26:16', 1, 'upgrade*2.99.1*0*upgrade2.99.1'),
(24, 'sql*2.99.2*0*2.99.2/saved_searches.sql', '', '0000-00-00 00:00:00', 1, NULL),
(25, 'sql*2.99.2*0*2.99.2/transactions.sql', '', '0000-00-00 00:00:00', 1, NULL),
(26, 'sql*2.99.2*0*2.99.2/field_mandatory.sql', '', '0000-00-00 00:00:00', 1, NULL),
(27, 'sql*2.99.2*0*2.99.2/fieldsets_system.sql', '', '0000-00-00 00:00:00', 1, NULL),
(28, 'sql*2.99.2*0*2.99.2/permission_by_user_and_roles.sql', '', '0000-00-00 00:00:00', 1, NULL),
(29, 'sql*2.99.2*0*2.99.2/disabled_metadata.sql', '', '0000-00-00 00:00:00', 1, NULL),
(30, 'sql*2.99.2*0*2.99.2/searchable_text.sql', '', '0000-00-00 00:00:00', 1, NULL),
(31, 'sql*2.99.2*0*2.99.2/workflow.sql', '', '0000-00-00 00:00:00', 1, NULL),
(32, 'sql*2.99.2*1*2.99.2/1-constraints.sql', '', '0000-00-00 00:00:00', 1, NULL),
(33, 'sql*2.99.3*0*2.99.3/notifications.sql', '', '0000-00-00 00:00:00', 1, NULL),
(34, 'sql*2.99.3*0*2.99.3/last_modified_user.sql', '', '0000-00-00 00:00:00', 1, NULL),
(35, 'sql*2.99.3*0*2.99.3/authentication_sources.sql', '', '0000-00-00 00:00:00', 1, NULL),
(36, 'sql*2.99.3*0*2.99.3/document_fields_constraints.sql', '', '0000-00-00 00:00:00', 1, NULL),
(37, 'sql*2.99.5*0*2.99.5/dashlet_disabling.sql', '', '0000-00-00 00:00:00', 1, NULL),
(38, 'sql*2.99.5*0*2.99.5/role_allocations.sql', '', '0000-00-00 00:00:00', 1, NULL),
(39, 'sql*2.99.5*0*2.99.5/transaction_namespaces.sql', '', '0000-00-00 00:00:00', 1, NULL),
(40, 'sql*2.99.5*0*2.99.5/fieldset_field_descriptions.sql', '', '0000-00-00 00:00:00', 1, NULL),
(41, 'sql*2.99.5*0*2.99.5/role_changes.sql', '', '0000-00-00 00:00:00', 1, NULL),
(42, 'sql*2.99.6*0*2.99.6/table_cleanup.sql', 'Database upgrade to version 2.99.6: Table cleanup', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(43, 'sql*2.99.6*0*2.99.6/plugin-registration.sql', 'Database upgrade to version 2.99.6: Plugin-registration', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(44, 'sql*2.99.7*0*2.99.7/documents_normalisation.sql', 'Database upgrade to version 2.99.7: Documents normalisation', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(45, 'sql*2.99.7*0*2.99.7/help_replacement.sql', 'Database upgrade to version 2.99.7: Help replacement', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(46, 'sql*2.99.7*0*2.99.7/table_cleanup.sql', 'Database upgrade to version 2.99.7: Table cleanup', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(47, 'func*2.99.7*1*normaliseDocuments', 'Normalise the documents table', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(48, 'sql*2.99.7*10*2.99.7/10-documents_normalisation.sql', 'Database upgrade to version 2.99.7: Documents normalisation', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(49, 'sql*2.99.7*20*2.99.7/20-fields.sql', 'Database upgrade to version 2.99.7: Fields', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(50, 'upgrade*2.99.7*99*upgrade2.99.7', 'Upgrade from version 2.99.5 to 2.99.7', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7'),
(51, 'sql*2.99.7*0*2.99.7/discussion.sql', '', '0000-00-00 00:00:00', 1, NULL),
(52, 'func*2.99.7*-1*applyDiscussionUpgrade', 'func upgrade to version 2.99.7 phase -1', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(53, 'sql*2.99.8*0*2.99.8/mime_types.sql', 'Database upgrade to version 2.99.8: Mime types', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(54, 'sql*2.99.8*0*2.99.8/category-correction.sql', 'Database upgrade to version 2.99.8: Category-correction', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(55, 'sql*2.99.8*0*2.99.8/trigger_selection.sql', 'Database upgrade to version 2.99.8: Trigger selection', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(56, 'sql*2.99.8*0*2.99.8/units.sql', 'Database upgrade to version 2.99.8: Units', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(57, 'sql*2.99.8*0*2.99.8/type_workflow_map.sql', 'Database upgrade to version 2.99.8: Type workflow map', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(58, 'sql*2.99.8*0*2.99.8/disabled_documenttypes.sql', 'Database upgrade to version 2.99.8: Disabled documenttypes', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(59, 'func*2.99.8*1*fixUnits', 'func upgrade to version 2.99.8 phase 1', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(60, 'sql*2.99.8*10*2.99.8/10-units.sql', 'Database upgrade to version 2.99.8: Units', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(61, 'sql*2.99.8*15*2.99.8/15-status.sql', 'Database upgrade to version 2.99.8: Status', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(62, 'sql*2.99.8*20*2.99.8/20-state_permission_assignments.sql', 'Database upgrade to version 2.99.8: State permission assignments', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(63, 'sql*2.99.8*25*2.99.8/25-authentication_details.sql', 'Database upgrade to version 2.99.8: Authentication details', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(64, 'upgrade*2.99.8*99*upgrade2.99.8', 'Upgrade from version 2.99.7 to 2.99.8', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8'),
(65, 'func*2.99.9*0*createSecurityDeletePermissions', 'Create the Core: Manage Security and Core: Delete permissions', '2006-02-28 09:23:21', 1, 'upgrade*3.0*99*upgrade3.0'),
(66, 'func*2.99.9*0*createLdapAuthenticationProvider', 'Create an LDAP authentication source based on your KT2 LDAP settings (must keep copy of config/environment.php to work)', '2006-02-28 09:23:21', 1, 'upgrade*3.0*99*upgrade3.0'),
(67, 'sql*2.99.9*0*2.99.9/mimetype-friendly.sql', 'Database upgrade to version 2.99.9: Mimetype-friendly', '2006-02-28 09:23:21', 1, 'upgrade*3.0*99*upgrade3.0'),
(68, 'sql*2.99.9*5*2.99.9/5-opendocument-mime-types.sql', 'Database upgrade to version 2.99.9: Opendocument-mime-types', '2006-02-28 09:23:21', 1, 'upgrade*3.0*99*upgrade3.0'),
(69, 'sql*3.0*0*3.0/zipfile-mimetype.sql', 'Database upgrade to version 3.0: Zipfile-mimetype', '2006-02-28 09:23:21', 1, 'upgrade*3.0*99*upgrade3.0'),
(70, 'upgrade*3.0*99*upgrade3.0', 'Upgrade from version 2.99.8 to 3.0', '2006-02-28 09:23:21', 1, 'upgrade*3.0*99*upgrade3.0'),
(71, 'sql*3.0.1.1*0*3.0.1.1/document_role_allocations.sql', 'Database upgrade to version 3.0.1.1: Document role allocations', '2006-03-28 11:22:19', 1, 'upgrade*3.0.1.1*99*upgrade3.0.1.1'),
(72, 'upgrade*3.0.1.1*99*upgrade3.0.1.1', 'Upgrade from version 3.0 to 3.0.1.1', '2006-03-28 11:22:19', 1, 'upgrade*3.0.1.1*99*upgrade3.0.1.1'),
(73, 'sql*3.0.1.2*0*3.0.1.2/user_more_authentication_details.sql', 'Database upgrade to version 3.0.1.2: User more authentication details', '2006-04-07 16:50:28', 1, 'upgrade*3.0.1.2*99*upgrade3.0.1.2'),
(74, 'upgrade*3.0.1.2*99*upgrade3.0.1.2', 'Upgrade from version 3.0.1.1 to 3.0.1.2', '2006-04-07 16:50:28', 1, 'upgrade*3.0.1.2*99*upgrade3.0.1.2'),
(75, 'sql*3.0.1.2*0*3.0.1.2/owner_role_move.sql', 'Database upgrade to version 3.0.1.2: Owner role move', '2006-04-18 11:06:34', 1, 'upgrade*3.0.1.4*99*upgrade3.0.1.4'),
(76, 'func*3.0.1.3*0*addTransactionTypes3013', 'Add new folder transaction types', '2006-04-18 11:06:34', 1, 'upgrade*3.0.1.4*99*upgrade3.0.1.4'),
(77, 'sql*3.0.1.3*0*3.0.1.3/user_history.sql', 'Database upgrade to version 3.0.1.3: User history', '2006-04-18 11:06:34', 1, 'upgrade*3.0.1.4*99*upgrade3.0.1.4'),
(78, 'sql*3.0.1.3*0*3.0.1.3/folder_transactions.sql', 'Database upgrade to version 3.0.1.3: Folder transactions', '2006-04-18 11:06:34', 1, 'upgrade*3.0.1.4*99*upgrade3.0.1.4'),
(79, 'sql*3.0.1.3*0*3.0.1.3/plugin-unavailable.sql', 'Database upgrade to version 3.0.1.3: Plugin-unavailable', '2006-04-18 11:06:34', 1, 'upgrade*3.0.1.4*99*upgrade3.0.1.4'),
(80, 'func*3.0.1.4*0*createWorkflowPermission', 'Create the Core: Manage Workflow', '2006-04-18 11:06:34', 1, 'upgrade*3.0.1.4*99*upgrade3.0.1.4'),
(81, 'upgrade*3.0.1.4*99*upgrade3.0.1.4', 'Upgrade from version 3.0.1.2 to 3.0.1.4', '2006-04-18 11:06:34', 1, 'upgrade*3.0.1.4*99*upgrade3.0.1.4'),
(82, 'sql*3.0.1.5*0*3.0.1.5/anonymous-user.sql', 'Database upgrade to version 3.0.1.5: Anonymous-user', '2006-04-18 12:38:41', 1, 'upgrade*3.0.1.5*99*upgrade3.0.1.5'),
(83, 'upgrade*3.0.1.5*99*upgrade3.0.1.5', 'Upgrade from version 3.0.1.4 to 3.0.1.5', '2006-04-18 12:38:41', 1, 'upgrade*3.0.1.5*99*upgrade3.0.1.5'),
(84, 'sql*3.0.1.6*0*3.0.1.6/workflow-into-metadata.sql', 'Database upgrade to version 3.0.1.6: Workflow-into-metadata', '2006-04-20 14:22:24', 1, 'upgrade*3.0.1.6*99*upgrade3.0.1.6'),
(85, 'upgrade*3.0.1.6*99*upgrade3.0.1.6', 'Upgrade from version 3.0.1.5 to 3.0.1.6', '2006-04-20 14:22:24', 1, 'upgrade*3.0.1.6*99*upgrade3.0.1.6'),
(86, 'sql*3.0.1.7*0*3.0.1.7/session_id.sql', 'Database upgrade to version 3.0.1.7: Session id', '2006-04-20 17:03:55', 1, 'upgrade*3.0.1.7*99*upgrade3.0.1.7'),
(87, 'upgrade*3.0.1.7*99*upgrade3.0.1.7', 'Upgrade from version 3.0.1.6 to 3.0.1.7', '2006-04-20 17:03:56', 1, 'upgrade*3.0.1.7*99*upgrade3.0.1.7'),
(88, 'sql*3.0.1.8*0*3.0.1.8/friendly-plugins.sql', 'Database upgrade to version 3.0.1.8: Friendly-plugins', '2006-04-23 12:54:12', 1, 'upgrade*3.0.1.8*99*upgrade3.0.1.8'),
(89, 'sql*3.0.1.8*0*3.0.1.8/longer-text.sql', 'Database upgrade to version 3.0.1.8: Longer-text', '2006-04-23 12:54:12', 1, 'upgrade*3.0.1.8*99*upgrade3.0.1.8'),
(90, 'sql*3.0.1.8*0*3.0.1.8/admin-mode-logging.sql', 'Database upgrade to version 3.0.1.8: Admin-mode-logging', '2006-04-23 12:54:12', 1, 'upgrade*3.0.1.8*99*upgrade3.0.1.8'),
(91, 'upgrade*3.0.1.8*99*upgrade3.0.1.8', 'Upgrade from version 3.0.1.7 to 3.0.1.8', '2006-04-23 12:54:12', 1, 'upgrade*3.0.1.8*99*upgrade3.0.1.8'),
(92, 'upgrade*3.0.2*99*upgrade3.0.2', 'Upgrade from version 3.0.1.8 to 3.0.2', '2006-05-02 10:08:13', 1, 'upgrade*3.0.2*99*upgrade3.0.2'),
(93, 'sql*3.0.2.1*0*3.0.2.1/disclaimer-help-files.sql', 'Database upgrade to version 3.0.2.1: Disclaimer-help-files', '2006-05-25 16:04:23', 1, 'upgrade*3.0.2.2*99*upgrade3.0.2.2'),
(94, 'sql*3.0.2.2*0*3.0.2.2/folder_search.sql', 'Database upgrade to version 3.0.2.2: Folder search', '2006-05-25 16:04:23', 1, 'upgrade*3.0.2.2*99*upgrade3.0.2.2'),
(95, 'upgrade*3.0.2.2*99*upgrade3.0.2.2', 'Upgrade from version 3.0.2 to 3.0.2.2', '2006-05-25 16:04:24', 1, 'upgrade*3.0.2.2*99*upgrade3.0.2.2'),
(96, 'sql*3.0.2.3*0*3.0.2.3/msi-filetype.sql', 'Database upgrade to version 3.0.2.3: Msi-filetype', '2006-05-30 10:55:58', 1, 'upgrade*3.0.2.4*99*upgrade3.0.2.4'),
(97, 'sql*3.0.2.4*0*3.0.2.4/discussion-fulltext.sql', 'Database upgrade to version 3.0.2.4: Discussion-fulltext', '2006-05-30 10:55:59', 1, 'upgrade*3.0.2.4*99*upgrade3.0.2.4'),
(98, 'upgrade*3.0.2.4*99*upgrade3.0.2.4', 'Upgrade from version 3.0.2.2 to 3.0.2.4', '2006-05-30 10:55:59', 1, 'upgrade*3.0.2.4*99*upgrade3.0.2.4'),
(99, 'upgrade*3.0.3*99*upgrade3.0.3', 'Upgrade from version 3.0.2.4 to 3.0.3', '2006-05-31 13:02:04', 1, 'upgrade*3.0.3*99*upgrade3.0.3'),
(100, 'sql*3.0.3.1*0*3.0.3.1/utf8.sql', 'Database upgrade to version 3.0.3.1: Utf8', '2006-07-12 12:00:33', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(101, 'sql*3.0.3.1*0*3.0.3.1/document_immutable.sql', 'Database upgrade to version 3.0.3.1: Document immutable', '2006-07-12 12:00:33', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(102, 'sql*3.0.3.1*0*3.0.3.1/workflow-triggers.sql', 'Database upgrade to version 3.0.3.1: Workflow-triggers', '2006-07-12 12:00:33', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(103, 'func*3.0.3.2*0*createFolderDetailsPermission', 'Create the Core: Folder Details permission', '2006-07-12 12:00:33', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(104, 'func*3.0.3.3*0*generateWorkflowTriggers', 'Migrate old in-transition guards to triggers', '2006-07-12 12:00:33', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(105, 'sql*3.0.3.4*0*3.0.3.4/column_entries.sql', 'Database upgrade to version 3.0.3.4: Column entries', '2006-07-12 12:00:33', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(106, 'sql*3.0.3.4*0*3.0.3.4/bulk_export_transaction.sql', 'Database upgrade to version 3.0.3.4: Bulk export transaction', '2006-07-12 12:00:33', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(107, 'upgrade*3.0.3.4*99*upgrade3.0.3.4', 'Upgrade from version 3.0.3 to 3.0.3.4', '2006-07-12 12:00:34', 1, 'upgrade*3.0.3.4*99*upgrade3.0.3.4'),
(108, 'sql*3.0.3.5*0*3.0.3.5/notifications_data_text.sql', 'Database upgrade to version 3.0.3.5: Notifications data text', '2006-07-14 15:26:49', 1, 'upgrade*3.0.3.5*99*upgrade3.0.3.5'),
(109, 'upgrade*3.0.3.5*99*upgrade3.0.3.5', 'Upgrade from version 3.0.3.4 to 3.0.3.5', '2006-07-14 15:26:49', 1, 'upgrade*3.0.3.5*99*upgrade3.0.3.5'),
(110, 'sql*3.0.3.6*0*3.0.3.6/document-restore.sql', 'Database upgrade to version 3.0.3.6: Document-restore', '2006-07-26 11:48:28', 1, 'upgrade*3.0.3.7*99*upgrade3.0.3.7'),
(111, 'func*3.0.3.7*0*rebuildAllPermissions', 'Rebuild all permissions to ensure correct functioning of permission-definitions.', '2006-07-26 11:48:28', 1, 'upgrade*3.0.3.7*99*upgrade3.0.3.7'),
(112, 'upgrade*3.0.3.7*99*upgrade3.0.3.7', 'Upgrade from version 3.0.3.5 to 3.0.3.7', '2006-07-26 11:48:28', 1, 'upgrade*3.0.3.7*99*upgrade3.0.3.7'),
(113, 'upgrade*3.1*99*upgrade3.1', 'Upgrade from version 3.0.3.7 to 3.1', '2006-07-31 10:41:12', 1, 'upgrade*3.1*99*upgrade3.1'),
(114, 'sql*3.1.1*0*3.1.1/parentless-documents.sql', 'Database upgrade to version 3.1.1: Parentless-documents', '2006-08-15 11:58:07', 1, 'upgrade*3.1.1*99*upgrade3.1.1'),
(115, 'upgrade*3.1.1*99*upgrade3.1.1', 'Upgrade from version 3.1 to 3.1.1', '2006-08-15 11:58:07', 1, 'upgrade*3.1.1*99*upgrade3.1.1'),
(116, 'sql*3.1.2*0*3.1.2/user-disable.sql', 'Database upgrade to version 3.1.2: User-disable', '2006-09-08 17:08:26', 1, 'upgrade*3.1.2*99*upgrade3.1.2'),
(117, 'upgrade*3.1.2*99*upgrade3.1.2', 'Upgrade from version 3.1.1 to 3.1.2', '2006-09-08 17:08:26', 1, 'upgrade*3.1.2*99*upgrade3.1.2'),
(118, 'func*3.1.5*0*upgradeSavedSearches', 'Upgrade saved searches to use namespaces instead of integer ids', '2006-10-17 12:09:45', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(119, 'sql*3.1.6*0*3.1.6/interceptor_instances.sql', 'Database upgrade to version 3.1.6: Interceptor instances', '2006-10-17 12:09:45', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(120, 'sql*3.1.6*0*3.1.6/workflow-sanity.sql', 'Database upgrade to version 3.1.6: Workflow-sanity', '2006-10-17 12:09:45', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(121, 'sql*3.1.6.2*0*3.1.6.2/workflow_state_disabled_actions.sql', 'Database upgrade to version 3.1.6.2: Workflow state disabled actions', '2006-10-17 12:09:45', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(122, 'sql*3.1.6.2*0*3.1.6.2/folder_owner_role.sql', 'Database upgrade to version 3.1.6.2: Folder owner role', '2006-10-17 12:09:45', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(123, 'func*3.1.6.3*0*cleanupGroupMembership', 'Cleanup any old references to missing groups, etc.', '2006-10-17 12:09:45', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(124, 'sql*3.1.6.3*0*3.1.6.3/groups-integrity.sql', 'Database upgrade to version 3.1.6.3: Groups-integrity', '2006-10-17 12:09:46', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(125, 'sql*3.1.6.5*0*3.1.6.5/workflow-state-referencefixes.sql', 'Database upgrade to version 3.1.6.5: Workflow-state-referencefixes', '2006-10-17 12:09:46', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(126, 'sql*3.1.6.6*0*3.1.6.6/copy_transaction.sql', 'Database upgrade to version 3.1.6.6: Copy transaction', '2006-10-17 12:09:46', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(127, 'sql*3.1.6.7*0*3.1.6.7/sane-names-for-stuff.sql', 'Database upgrade to version 3.1.6.7: Sane-names-for-stuff', '2006-10-17 12:09:46', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(128, 'upgrade*3.1.6.7*99*upgrade3.1.6.7', 'Upgrade from version 3.1.2 to 3.1.6.7', '2006-10-17 12:09:46', 1, 'upgrade*3.1.6.7*99*upgrade3.1.6.7'),
(129, 'sql*3.3.0.1*0*3.3.0.1/system-settings-to-text.sql', 'Database upgrade to version 3.3.0.1: System-settings-to-text', '2007-01-28 23:49:52', 1, 'upgrade*3.3.1*99*upgrade3.3.1'),
(130, 'upgrade*3.3.0.1*99*upgrade3.3.0.1', 'Upgrade from version 3.1.6.7 to 3.3.0.1', '2006-10-30 12:49:33', 1, 'upgrade*3.3.0.1*99*upgrade3.3.0.1'),
(131, 'sql*3.3.1*0*3.3.1/rss.sql', 'Database upgrade to version 3.3.1: Rss', '2007-01-28 23:49:52', 1, 'upgrade*3.3.1*99*upgrade3.3.1'),
(132, 'upgrade*3.3.1*99*upgrade3.3.1', 'Upgrade from version 3.3.0.1 to 3.3.1', '2007-01-28 23:49:52', 1, 'upgrade*3.3.1*99*upgrade3.3.1'),
(133, 'sql*3.3.2*0*3.3.2/tagclouds.sql', 'Database upgrade to version 3.3.2: Tagclouds', '2007-02-23 11:55:09', 1, 'upgrade*3.3.2*99*upgrade3.3.2'),
(134, 'upgrade*3.3.2*99*upgrade3.3.2', 'Upgrade from version 3.3.1 to 3.3.2', '2007-02-23 11:55:09', 1, 'upgrade*3.3.2*99*upgrade3.3.2'),
(135, 'sql*3.4.0*0*3.4.0/upload_download.sql', 'Upgrade to version 3.4.0: Upload download', '2007-04-17 00:00:00', 1, 'upgrade*3.4.0*99*upgrade3.4.0'),
(136, 'upgrade*3.4.0*99*upgrade3.4.0', 'Upgrade from version 3.3.2 to 3.4.0', '2007-04-17 00:00:00', 1, 'upgrade*3.4.0*99*upgrade3.4.0'),
(137, 'sql*3.4.5*0*3.4.5/plugin_helper.sql', 'Create the plugin helper table.', '2007-11-20 00:00:00', 1, 'upgrade*3.4.5*99*upgrade3.4.5'),
(138, 'upgrade*3.4.5*99*upgrade3.4.5', 'Upgrade from version 3.4.0 to 3.4.5', '2007-11-20 00:00:00', 1, 'upgrade*3.4.5*99*upgrade3.4.5'),
(139, 'sql*3.4.6*0*3.4.6/remove_backslashes.sql', 'Remove backslashes.', '2007-11-20 00:00:00', 1, 'upgrade*3.4.6*99*upgrade3.4.6'),
(140, 'upgrade*3.4.6*99*upgrade3.4.6', 'Upgrade from version 3.4.5 to 3.4.6', '2007-11-20 00:00:00', 1, 'upgrade*3.4.6*99*upgrade3.4.6'),
(141, 'sql*3.5.0*0*3.5.0/admin_version_path_update.sql', 'Update Admin Version Plugin Path', '2007-08-28 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(142, 'sql*3.5.0*0*3.5.0/saved_searches.sql', 'Database upgrade to version 3.5.0: Saved searches', '2007-09-25 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(143, 'sql*3.5.0*0*3.5.0/index_files.sql', 'Database upgrade to version 3.5.0: Index files', '2007-09-25 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(144, 'sql*3.5.0*0*3.5.0/search_ranking.sql', 'Database upgrade to version 3.5.0: Search ranking', '2007-09-25 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(145, 'sql*3.5.0*0*3.5.0/document_checkout.sql', 'Database upgrade to version 3.5.0: Document checkout', '2007-09-25 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(146, 'func*3.5.0*0*cleanupOldKTAdminVersionNotifier', 'Cleanup any old files from the old KTAdminVersionNotifier', '2007-09-25 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(147, 'upgrade*3.5.0*99*upgrade3.5.0', 'Upgrade from version 3.4.0 to 3.5.0', '2007-09-25 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(148, 'sql*3.5.0*0*3.5.0/folder_descendants.sql', 'Database upgrade to version 3.5.0: Folder descendants', '2007-10-11 17:41:32', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(149, 'sql*3.5.0*0*3.5.0/relation_friendly.sql', 'Database upgrade to version 3.5.0: Relation friendly', '2007-10-11 17:41:33', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(150, 'sql*3.5.0*0*3.5.0/plugin_rss_engine.sql', 'Database upgrade to version 3.5.0: Plugin rss engine', '2007-10-11 17:41:33', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(151, 'sql*3.5.0*0*3.5.0/document_transaction_type.sql', 'Database upgrade to version 3.5.0: Document transaction type', '2007-10-11 17:41:33', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(152, 'sql*3.5.0*0*3.5.0/scheduler_tables.sql', 'Database upgrade to version 3.5.0: Scheduler tables', '2007-10-23 15:40:56', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(153, 'func*3.5.0*0*registerIndexingTasks', 'Register the required indexing background tasks', '2007-10-23 15:40:56', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(154, 'func*3.5.0*0*updateConfigFile35', 'Update the config.ini file for 3.5', '2007-10-23 15:40:56', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(155, 'sql*3.5.0*0*3.5.0/mime_types.sql', 'Database upgrade to version 3.5.0: Mime types', '2007-10-23 15:40:58', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(156, 'upgrade*3.5.0*99*upgrade3.5.0', 'Upgrade from version 3.4.5 to 3.5.0', '2007-11-21 00:00:00', 1, 'upgrade*3.5.0*99*upgrade3.5.0'),
(157, 'sql*3.5.1*0*3.5.1/indexing_tasks_registration.sql', 'Register indexing tasks with the scheduler.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.1*99*upgrade3.5.1'),
(158, 'sql*3.5.1*0*3.5.1/png_mime_type.sql', 'Register PNG mimetype.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.1*99*upgrade3.5.1'),
(159, 'upgrade*3.5.1*99*upgrade3.5.1', 'Upgrade from version 3.5.0 to 3.5.1', '2007-11-21 00:00:00', 1, 'upgrade*3.5.1*99*upgrade3.5.1'),
(160, 'sql*3.5.2*0*3.5.2/document_transactions.sql', 'Updates document_transactions table. Changes chars to varchars.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(161, 'sql*3.5.2*0*3.5.2/metadata_length.sql', 'Updates metadata length. Changes chars to varchars.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(162, 'sql*3.5.2*0*3.5.2/scheduler_tasks.sql', 'Initialise some scheduler tasks.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(163, 'sql*3.5.2*0*3.5.2/csv_mime.sql', 'Update mime types for CSV files.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(164, 'func*3.5.2*1*setStorageEngine', 'Recreate db integrity: Set storage engine to InnoDB for transaction safety', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(165, 'func*3.5.2*2*dropForeignKeys', 'Recreate db integrity: Drop foreign keys on the database', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(166, 'func*3.5.2*3*dropPrimaryKeys', 'Recreate db integrity:Drop primary keys on the database', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(167, 'func*3.5.2*4*dropIndexes', 'Recreate db integrity:Drop indexes on the database', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(168, 'func*3.5.2*5*createPrimaryKeys', 'Recreate db integrity:Create primary keys on the database', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(169, 'func*3.5.2*6*createForeignKeys', 'Recreate db integrity:Create foreign keys on the database', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(170, 'func*3.5.2*7*createIndexes', 'Recreate db integrity:Create indexes on the database', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(171, 'func*3.5.2*0*removeSlashesFromObjects', 'Remove slashes from documents and folders', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(172, 'sql*3.5.2*0*3.5.2/plugins_orderby.sql', 'Plugins orderby update', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(173, 'sql*3.5.2*0*3.5.2/oem_no.sql', 'Database upgrade to version 3.5.2: Oem no', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(174, 'sql*3.5.2*0*3.5.2/document_link.sql', 'Document Link update', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(175, 'sql*3.5.2*0*3.5.2/index_file_status_message.sql', 'Index file status message update', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(176, 'sql*3.5.2*0*3.5.2/clean_plugin_helper.sql', 'Clean out the plugin helper table.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(177, 'sql*3.5.2*0*3.5.2/openxml_mime_types.sql', 'Add the OpenXML mimetypes.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(178, 'sql*3.5.2*0*3.5.2/rss_plugin_title.sql', 'Increase size of RSS Title.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(179, 'sql*3.5.2*0*3.5.2/temp_cleanup.sql', 'Adds background script to clean up temporary index files.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(180, 'sql*3.5.2*0*3.5.2/scheduler_permissions.sql', 'Update scheduler permissions..', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(181, 'sql*3.5.2*0*3.5.2/mime_type_update.sql', 'Update MIME types.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(182, 'sql*3.5.2*0*3.5.2/zdashboard_tasks.sql', 'Update Dashboard tasks.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(183, 'sql*3.5.2*0*3.5.2/zdashboard_tasks2.sql', 'Update more Dashboard tasks.', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(184, 'upgrade*3.5.2*99*upgrade3.5.2', 'Upgrade from version 3.5.1 to 3.5.2', '2007-11-21 00:00:00', 1, 'upgrade*3.5.2*99*upgrade3.5.2'),
(185, 'sql*3.5.3*0*3.5.3/add_autoinc.sql', 'Add autoincrement.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(186, 'sql*3.5.3*0*3.5.3/content_md5hash.sql', 'Add Content md5 hash.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(187, 'sql*3.5.3*0*3.5.3/document_field_position.sql', 'Document field postion update.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(188, 'sql*3.5.3*0*3.5.3/shortcuts.sql', 'Shortcuts update.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(189, 'sql*3.5.3*0*3.5.3/config_settings.sql', 'Configuration settings update.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(190, 'sql*3.5.3*0*3.5.3/doc_checked_out_user_id.sql', 'Checkedout user ID update.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(191, 'sql*3.5.3*0*3.5.3/indexer_updates.sql', 'Indexer updates.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(192, 'sql*3.5.3*0*3.5.3/db_optimizations.sql', 'Database optimizations.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(193, 'sql*3.5.3*0*3.5.3/del_adminversion_plugin.sql', 'Remove the old Admin Version Notifier.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(194, 'func*3.5.3*0*removeAdminVersionNotifier', 'Remove the old Admin Version Notifier files', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(195, 'sql*3.5.3*0*3.5.3/del_oldsearch_plugins.sql', 'Remove the old Search Plugins SQL.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(196, 'func*3.5.3*0*removeOldSearchPlugins', 'Remove the old Search Plugins files', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(197, 'sql*3.5.3*0*3.5.3/add_autoinc.sql', 'Add auto increment to tables SQL.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(198, 'func*3.5.3*0*addAutoIncrementToTables', 'Add auto increment.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(199, 'sql*3.5.3*0*3.5.3/length_config_setting.sql', 'Add configurable name length.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(200, 'sql*3.5.3*0*3.5.3/active_session_apptype.sql', 'Add active session application type.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(201, 'sql*3.5.3*0*3.5.3/subscriptions.sql', 'Extending subscription to subfolders.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(202, 'sql*3.5.3*0*3.5.3/doc_transactions.sql', 'Fix versions in transaction history.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(203, 'sql*3.5.3*0*3.5.3/saved_search.sql', 'Fix saved search table to support long expressions.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(204, 'sql*3.5.3*0*3.5.3/preview_column.sql', 'Adjust Preview Column.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(205, 'sql*3.5.3*0*3.5.3/tag_cloud.sql', 'Update TagCloud descritption.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(206, 'sql*3.5.3*0*3.5.3/doc_tran_user_index.sql', 'Add index on user_id to document transactions table.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(207, 'upgrade*3.5.3*99*upgrade3.5.3', 'Upgrade from version 3.5.2 to 3.5.3', '2008-07-30 00:00:00', 1, 'upgrade*3.5.3*99*upgrade3.5.3'),
(208, 'func*3.5.4*7*createIndexes', 'Recreate db integrity:Create indexes on the database', '2008-10-01 00:00:00', 1, 'upgrade*3.5.4*99*upgrade3.5.4'),
(209, 'sql*3.5.4*0*3.5.4/max_sql_search_results.sql', 'Add configurable maximum results for SQL search queries.', '2008-07-30 00:00:00', 1, 'upgrade*3.5.4*99*upgrade3.5.4'),
(210, 'sql*3.5.4*0*3.5.4/server_config_settings.sql', 'Create the configuration settings for the servers IP and port', '2008-11-25 00:00:00', 1, 'upgrade*3.5.4*99*upgrade3.5.4'),
(211, 'func*3.5.4*0*removeOldFilesAndFolders354', 'Remove old files and folders that are no longer needed.', '2008-10-01 00:00:00', 1, 'upgrade*3.5.4*99*upgrade3.5.4'),
(212, 'func*3.5.4*0*updateServerConfigSettings', 'Update the configuration settings for the server with the correct port', '2008-11-25 00:00:00', 1, 'upgrade*3.5.4*99*upgrade3.5.4'),
(213, 'upgrade*3.5.4*99*upgrade3.5.4', 'Upgrade from version 3.5.3 to 3.5.4', '2008-10-01 00:00:00', 1, 'upgrade*3.5.4*99*upgrade3.5.4'),
(214, 'func*3.5.4a*0*removeOldFilesAndFolders354a', 'Remove old files and folders that are no longer needed.', '2008-10-01 00:00:00', 1, 'upgrade*3.5.4a*99*upgrade3.5.4a'),
(215, 'func*3.5.4a*0*removeOldFilesAndFolders354a1', 'Remove old files and folders that are no longer needed.', '2008-10-01 00:00:00', 1, 'upgrade*3.5.4a*99*upgrade3.5.4a'),
(216, 'upgrade*3.5.4a*99*upgrade3.5.4a', 'Upgrade from version 3.5.4 to 3.5.4a', '2008-12-01 00:00:00', 1, 'upgrade*3.5.4a*99*upgrade3.5.4a'),
(217, 'sql*3.6*0*3.6.0/ldap_config_setting.sql', 'Database upgrade to version 3.6: Ldap config setting', '2009-01-01 00:00:00', 1, 'upgrade*3.6.0*99*upgrade3.6.0'),
(218, 'sql*3.6*0*3.6.0/download_queue.sql', 'Database upgrade to version 3.6: Download queue', '2009-01-01 00:00:00', 1, 'upgrade*3.6.0*99*upgrade3.6.0'),
(219, 'upgrade*3.6.0*99*upgrade3.6.0', 'Upgrade from version 3.5.4a to 3.6.0', '2009-01-01 00:00:00', 1, 'upgrade*3.6.0*99*upgrade3.6.0'),
(220, 'sql*3.6.1*0*3.6.1/search_ranking.sql', 'Database upgrade to version 3.6.1: Search ranking', '2009-04-01 00:00:00', 1, 'upgrade*3.6.1*99*upgrade3.6.1'),
(221, 'upgrade*3.6.1*99*upgrade3.6.1', 'Upgrade from version 3.6.0 to 3.6.1', '2009-04-01 00:00:00', 1, 'upgrade*3.6.1*99*upgrade3.6.1'),
(222, 'func*3.6.1*0*removeSlashesFromObjects', 'Remove slashes from documents and folders', '2009-08-25 10:33:16', 1, 'upgrade*3.6.3*99*upgrade3.6.3'),
(223, 'sql*3.6.2*0*3.6.2/data_types.sql', 'Database upgrade to version 3.6.2: Data types', '2009-08-25 10:33:17', 1, 'upgrade*3.6.3*99*upgrade3.6.3'),
(224, 'sql*3.6.2*0*3.6.2/folders.sql', 'Database upgrade to version 3.6.2: Folders', '2009-08-25 10:33:17', 1, 'upgrade*3.6.3*99*upgrade3.6.3'),
(225, 'upgrade*3.7.0*99*upgrade3.7.0', 'Upgrade from version 3.7 to 3.7.0', '2009-09-22 08:28:53', 1, 'upgrade*3.7.0*99*upgrade3.7.0');

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

--
-- Dumping data for table `uploaded_files`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `password`, `quota_max`, `quota_current`, `email`, `mobile`, `email_notification`, `sms_notification`, `authentication_details_s1`, `max_sessions`, `language_id`, `authentication_details_s2`, `authentication_source_id`, `authentication_details_b1`, `authentication_details_i2`, `authentication_details_d1`, `authentication_details_i1`, `authentication_details_d2`, `authentication_details_b2`, `last_login`, `disabled`) VALUES
(-2, 'anonymous', 'Anonymous', '---------------', 0, 0, NULL, NULL, 0, 0, NULL, 30000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(1, 'admin', 'Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', 1, 1, '', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2009-09-22 08:28:46', 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `users_groups_link`
--

INSERT INTO `users_groups_link` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `user_history`
--

INSERT INTO `user_history` (`id`, `datetime`, `user_id`, `action_namespace`, `comments`, `session_id`) VALUES
(1, '2009-08-25 10:33:07', 1, 'ktcore.user_history.login', 'Logged in from 127.0.0.1', 1),
(2, '2009-09-21 09:43:27', 1, 'ktcore.user_history.login', 'Logged in from 127.0.0.1', 2),
(3, '2009-09-21 09:45:46', 1, 'ktcore.user_history.login', 'Logged in from 127.0.0.1', 3),
(4, '2009-09-22 08:28:46', 1, 'ktcore.user_history.login', 'Logged in from 127.0.0.1', 4),
(5, '2009-08-25 10:53:07', 1, 'ktcore.user_history.timeout', 'Session timed out', 0),
(6, '2009-09-21 10:03:27', 1, 'ktcore.user_history.timeout', 'Session timed out', 0),
(7, '2009-09-21 10:05:46', 1, 'ktcore.user_history.timeout', 'Session timed out', 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `user_history_documents`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `user_history_folders`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `workflows`
--

INSERT INTO `workflows` (`id`, `name`, `human_name`, `start_state_id`, `enabled`) VALUES
(2, 'Review Process', 'Review Process', 2, 1),
(3, 'Generate Document', 'Generate Document', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `workflow_actions`
--

CREATE TABLE IF NOT EXISTS `workflow_actions` (
  `workflow_id` int(11) NOT NULL DEFAULT '0',
  `action_name` varchar(255) NOT NULL,
  PRIMARY KEY (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `workflow_actions`
--


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

--
-- Dumping data for table `workflow_documents`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `workflow_states`
--

INSERT INTO `workflow_states` (`id`, `workflow_id`, `name`, `human_name`, `inform_descriptor_id`, `manage_permissions`, `manage_actions`) VALUES
(2, 2, 'Draft', 'Draft', NULL, 0, 0),
(3, 2, 'Approval', 'Approval', NULL, 0, 0),
(4, 2, 'Published', 'Published', NULL, 0, 0),
(5, 3, 'Draft', 'Draft', NULL, 0, 0),
(6, 3, 'Final', 'Final', NULL, 0, 0),
(7, 3, 'Published', 'Published', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `workflow_state_actions`
--

CREATE TABLE IF NOT EXISTS `workflow_state_actions` (
  `state_id` int(11) NOT NULL DEFAULT '0',
  `action_name` varchar(255) NOT NULL,
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `workflow_state_actions`
--


-- --------------------------------------------------------

--
-- Table structure for table `workflow_state_disabled_actions`
--

CREATE TABLE IF NOT EXISTS `workflow_state_disabled_actions` (
  `state_id` int(11) NOT NULL DEFAULT '0',
  `action_name` varchar(255) NOT NULL,
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `workflow_state_disabled_actions`
--


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `workflow_state_permission_assignments`
--


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

--
-- Dumping data for table `workflow_state_transitions`
--

INSERT INTO `workflow_state_transitions` (`state_id`, `transition_id`) VALUES
(2, 2),
(3, 3),
(3, 4),
(5, 5),
(6, 6);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `workflow_transitions`
--

INSERT INTO `workflow_transitions` (`id`, `workflow_id`, `name`, `human_name`, `target_state_id`, `guard_permission_id`, `guard_group_id`, `guard_role_id`, `guard_condition_id`) VALUES
(2, 2, 'Request Approval', 'Request Approval', 3, NULL, NULL, NULL, NULL),
(3, 2, 'Reject', 'Reject', 2, NULL, NULL, NULL, NULL),
(4, 2, 'Approve', 'Approve', 4, NULL, NULL, NULL, NULL),
(5, 3, 'Draft Completed', 'Draft Completed', 6, NULL, NULL, NULL, NULL),
(6, 3, 'Publish', 'Publish', 7, NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `workflow_trigger_instances`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_active_sessions`
--

CREATE TABLE IF NOT EXISTS `zseq_active_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_active_sessions`
--

INSERT INTO `zseq_active_sessions` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_archive_restoration_request`
--

CREATE TABLE IF NOT EXISTS `zseq_archive_restoration_request` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_archive_restoration_request`
--

INSERT INTO `zseq_archive_restoration_request` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_archiving_settings`
--

CREATE TABLE IF NOT EXISTS `zseq_archiving_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_archiving_settings`
--

INSERT INTO `zseq_archiving_settings` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_archiving_type_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_archiving_type_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_archiving_type_lookup`
--

INSERT INTO `zseq_archiving_type_lookup` (`id`) VALUES
(2);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_authentication_sources`
--

CREATE TABLE IF NOT EXISTS `zseq_authentication_sources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_authentication_sources`
--

INSERT INTO `zseq_authentication_sources` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_baobab_keys`
--

CREATE TABLE IF NOT EXISTS `zseq_baobab_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_baobab_keys`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_baobab_user_keys`
--

CREATE TABLE IF NOT EXISTS `zseq_baobab_user_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_baobab_user_keys`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_column_entries`
--

CREATE TABLE IF NOT EXISTS `zseq_column_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_column_entries`
--

INSERT INTO `zseq_column_entries` (`id`) VALUES
(15);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_config_settings`
--

CREATE TABLE IF NOT EXISTS `zseq_config_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_config_settings`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_dashlet_disables`
--

CREATE TABLE IF NOT EXISTS `zseq_dashlet_disables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_dashlet_disables`
--

INSERT INTO `zseq_dashlet_disables` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_data_types`
--

CREATE TABLE IF NOT EXISTS `zseq_data_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_data_types`
--

INSERT INTO `zseq_data_types` (`id`) VALUES
(5);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_discussion_comments`
--

CREATE TABLE IF NOT EXISTS `zseq_discussion_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_discussion_comments`
--

INSERT INTO `zseq_discussion_comments` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_discussion_threads`
--

CREATE TABLE IF NOT EXISTS `zseq_discussion_threads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_discussion_threads`
--

INSERT INTO `zseq_discussion_threads` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_documents`
--

CREATE TABLE IF NOT EXISTS `zseq_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_documents`
--

INSERT INTO `zseq_documents` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_archiving_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_archiving_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_archiving_link`
--

INSERT INTO `zseq_document_archiving_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_content_version`
--

CREATE TABLE IF NOT EXISTS `zseq_document_content_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_content_version`
--

INSERT INTO `zseq_document_content_version` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_fields`
--

CREATE TABLE IF NOT EXISTS `zseq_document_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_fields`
--

INSERT INTO `zseq_document_fields` (`id`) VALUES
(5);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_fields_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_fields_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_fields_link`
--

INSERT INTO `zseq_document_fields_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_link`
--

INSERT INTO `zseq_document_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_link_types`
--

CREATE TABLE IF NOT EXISTS `zseq_document_link_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_link_types`
--

INSERT INTO `zseq_document_link_types` (`id`) VALUES
(5);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_metadata_version`
--

CREATE TABLE IF NOT EXISTS `zseq_document_metadata_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_metadata_version`
--

INSERT INTO `zseq_document_metadata_version` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_role_allocations`
--

CREATE TABLE IF NOT EXISTS `zseq_document_role_allocations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_role_allocations`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_subscriptions`
--

CREATE TABLE IF NOT EXISTS `zseq_document_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_subscriptions`
--

INSERT INTO `zseq_document_subscriptions` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_tags`
--

CREATE TABLE IF NOT EXISTS `zseq_document_tags` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `zseq_document_tags`
--

INSERT INTO `zseq_document_tags` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_transactions`
--

CREATE TABLE IF NOT EXISTS `zseq_document_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_transactions`
--

INSERT INTO `zseq_document_transactions` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_transaction_types_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_document_transaction_types_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_transaction_types_lookup`
--

INSERT INTO `zseq_document_transaction_types_lookup` (`id`) VALUES
(21);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_types_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_document_types_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_types_lookup`
--

INSERT INTO `zseq_document_types_lookup` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_type_fieldsets_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_type_fieldsets_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `zseq_document_type_fieldsets_link`
--

INSERT INTO `zseq_document_type_fieldsets_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_document_type_fields_link`
--

CREATE TABLE IF NOT EXISTS `zseq_document_type_fields_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_document_type_fields_link`
--

INSERT INTO `zseq_document_type_fields_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_fieldsets`
--

CREATE TABLE IF NOT EXISTS `zseq_fieldsets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `zseq_fieldsets`
--

INSERT INTO `zseq_fieldsets` (`id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_field_behaviours`
--

CREATE TABLE IF NOT EXISTS `zseq_field_behaviours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_field_behaviours`
--

INSERT INTO `zseq_field_behaviours` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_field_value_instances`
--

CREATE TABLE IF NOT EXISTS `zseq_field_value_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_field_value_instances`
--

INSERT INTO `zseq_field_value_instances` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folders`
--

CREATE TABLE IF NOT EXISTS `zseq_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `zseq_folders`
--

INSERT INTO `zseq_folders` (`id`) VALUES
(2);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folders_users_roles_link`
--

CREATE TABLE IF NOT EXISTS `zseq_folders_users_roles_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_folders_users_roles_link`
--

INSERT INTO `zseq_folders_users_roles_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folder_doctypes_link`
--

CREATE TABLE IF NOT EXISTS `zseq_folder_doctypes_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `zseq_folder_doctypes_link`
--

INSERT INTO `zseq_folder_doctypes_link` (`id`) VALUES
(2);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folder_subscriptions`
--

CREATE TABLE IF NOT EXISTS `zseq_folder_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_folder_subscriptions`
--

INSERT INTO `zseq_folder_subscriptions` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_folder_transactions`
--

CREATE TABLE IF NOT EXISTS `zseq_folder_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_folder_transactions`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_groups_groups_link`
--

CREATE TABLE IF NOT EXISTS `zseq_groups_groups_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_groups_groups_link`
--

INSERT INTO `zseq_groups_groups_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_groups_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_groups_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `zseq_groups_lookup`
--

INSERT INTO `zseq_groups_lookup` (`id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_help`
--

CREATE TABLE IF NOT EXISTS `zseq_help` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;

--
-- Dumping data for table `zseq_help`
--

INSERT INTO `zseq_help` (`id`) VALUES
(100);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_help_replacement`
--

CREATE TABLE IF NOT EXISTS `zseq_help_replacement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_help_replacement`
--

INSERT INTO `zseq_help_replacement` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_interceptor_instances`
--

CREATE TABLE IF NOT EXISTS `zseq_interceptor_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_interceptor_instances`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_links`
--

CREATE TABLE IF NOT EXISTS `zseq_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_links`
--

INSERT INTO `zseq_links` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_metadata_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_metadata_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `zseq_metadata_lookup`
--

INSERT INTO `zseq_metadata_lookup` (`id`) VALUES
(11);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_metadata_lookup_tree`
--

CREATE TABLE IF NOT EXISTS `zseq_metadata_lookup_tree` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_metadata_lookup_tree`
--

INSERT INTO `zseq_metadata_lookup_tree` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_mime_documents`
--

CREATE TABLE IF NOT EXISTS `zseq_mime_documents` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zseq_mime_documents`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_mime_extractors`
--

CREATE TABLE IF NOT EXISTS `zseq_mime_extractors` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zseq_mime_extractors`
--

INSERT INTO `zseq_mime_extractors` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_mime_types`
--

CREATE TABLE IF NOT EXISTS `zseq_mime_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=172 ;

--
-- Dumping data for table `zseq_mime_types`
--

INSERT INTO `zseq_mime_types` (`id`) VALUES
(171);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_news`
--

CREATE TABLE IF NOT EXISTS `zseq_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_news`
--

INSERT INTO `zseq_news` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_notifications`
--

CREATE TABLE IF NOT EXISTS `zseq_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_notifications`
--

INSERT INTO `zseq_notifications` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_organisations_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_organisations_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_organisations_lookup`
--

INSERT INTO `zseq_organisations_lookup` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permissions`
--

CREATE TABLE IF NOT EXISTS `zseq_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `zseq_permissions`
--

INSERT INTO `zseq_permissions` (`id`) VALUES
(8);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_assignments`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `zseq_permission_assignments`
--

INSERT INTO `zseq_permission_assignments` (`id`) VALUES
(8);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_descriptors`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_descriptors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `zseq_permission_descriptors`
--

INSERT INTO `zseq_permission_descriptors` (`id`) VALUES
(2);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_dynamic_conditions`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_dynamic_conditions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_permission_dynamic_conditions`
--

INSERT INTO `zseq_permission_dynamic_conditions` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_lookups`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_lookups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `zseq_permission_lookups`
--

INSERT INTO `zseq_permission_lookups` (`id`) VALUES
(5);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_lookup_assignments`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_lookup_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `zseq_permission_lookup_assignments`
--

INSERT INTO `zseq_permission_lookup_assignments` (`id`) VALUES
(24);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_permission_objects`
--

CREATE TABLE IF NOT EXISTS `zseq_permission_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_permission_objects`
--

INSERT INTO `zseq_permission_objects` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_plugins`
--

CREATE TABLE IF NOT EXISTS `zseq_plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `zseq_plugins`
--

INSERT INTO `zseq_plugins` (`id`) VALUES
(22);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_plugin_helper`
--

CREATE TABLE IF NOT EXISTS `zseq_plugin_helper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_plugin_helper`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_plugin_rss`
--

CREATE TABLE IF NOT EXISTS `zseq_plugin_rss` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_plugin_rss`
--

INSERT INTO `zseq_plugin_rss` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_quicklinks`
--

CREATE TABLE IF NOT EXISTS `zseq_quicklinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_quicklinks`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_roles`
--

CREATE TABLE IF NOT EXISTS `zseq_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `zseq_roles`
--

INSERT INTO `zseq_roles` (`id`) VALUES
(4);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_role_allocations`
--

CREATE TABLE IF NOT EXISTS `zseq_role_allocations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_role_allocations`
--

INSERT INTO `zseq_role_allocations` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_saved_searches`
--

CREATE TABLE IF NOT EXISTS `zseq_saved_searches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_saved_searches`
--

INSERT INTO `zseq_saved_searches` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_scheduler_tasks`
--

CREATE TABLE IF NOT EXISTS `zseq_scheduler_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `zseq_scheduler_tasks`
--

INSERT INTO `zseq_scheduler_tasks` (`id`) VALUES
(10);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_search_saved`
--

CREATE TABLE IF NOT EXISTS `zseq_search_saved` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zseq_search_saved`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_status_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_status_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `zseq_status_lookup`
--

INSERT INTO `zseq_status_lookup` (`id`) VALUES
(6);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_system_settings`
--

CREATE TABLE IF NOT EXISTS `zseq_system_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `zseq_system_settings`
--

INSERT INTO `zseq_system_settings` (`id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_tag_words`
--

CREATE TABLE IF NOT EXISTS `zseq_tag_words` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_tag_words`
--

INSERT INTO `zseq_tag_words` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_time_period`
--

CREATE TABLE IF NOT EXISTS `zseq_time_period` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_time_period`
--

INSERT INTO `zseq_time_period` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_time_unit_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_time_unit_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `zseq_time_unit_lookup`
--

INSERT INTO `zseq_time_unit_lookup` (`id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_units_lookup`
--

CREATE TABLE IF NOT EXISTS `zseq_units_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_units_lookup`
--

INSERT INTO `zseq_units_lookup` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_units_organisations_link`
--

CREATE TABLE IF NOT EXISTS `zseq_units_organisations_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `zseq_units_organisations_link`
--

INSERT INTO `zseq_units_organisations_link` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_upgrades`
--

CREATE TABLE IF NOT EXISTS `zseq_upgrades` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=222 ;

--
-- Dumping data for table `zseq_upgrades`
--

INSERT INTO `zseq_upgrades` (`id`) VALUES
(221);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_users`
--

CREATE TABLE IF NOT EXISTS `zseq_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `zseq_users`
--

INSERT INTO `zseq_users` (`id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_users_groups_link`
--

CREATE TABLE IF NOT EXISTS `zseq_users_groups_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `zseq_users_groups_link`
--

INSERT INTO `zseq_users_groups_link` (`id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_user_history`
--

CREATE TABLE IF NOT EXISTS `zseq_user_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_user_history`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_user_history_documents`
--

CREATE TABLE IF NOT EXISTS `zseq_user_history_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `zseq_user_history_documents`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_user_history_folders`
--

CREATE TABLE IF NOT EXISTS `zseq_user_history_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `zseq_user_history_folders`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflows`
--

CREATE TABLE IF NOT EXISTS `zseq_workflows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `zseq_workflows`
--

INSERT INTO `zseq_workflows` (`id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_states`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_states` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `zseq_workflow_states`
--

INSERT INTO `zseq_workflow_states` (`id`) VALUES
(7);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_state_disabled_actions`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_state_disabled_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_workflow_state_disabled_actions`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_state_permission_assignments`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_state_permission_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_workflow_state_permission_assignments`
--


-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_transitions`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_transitions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `zseq_workflow_transitions`
--

INSERT INTO `zseq_workflow_transitions` (`id`) VALUES
(6);

-- --------------------------------------------------------

--
-- Table structure for table `zseq_workflow_trigger_instances`
--

CREATE TABLE IF NOT EXISTS `zseq_workflow_trigger_instances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `zseq_workflow_trigger_instances`
--


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
  ADD CONSTRAINT `baobab_user_keys_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `baobab_user_keys_ibfk_4` FOREIGN KEY (`key_id`) REFERENCES `baobab_keys` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `folder_descendants_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `folder_descendants_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `mime_document_mapping_ibfk_1` FOREIGN KEY (`mime_type_id`) REFERENCES `mime_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mime_document_mapping_ibfk_2` FOREIGN KEY (`mime_document_id`) REFERENCES `mime_documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `permission_dynamic_assignments_ibfk_1` FOREIGN KEY (`dynamic_condition_id`) REFERENCES `permission_dynamic_conditions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_dynamic_assignments_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `search_document_user_link_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `search_document_user_link_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `workflow_state_transitions_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `workflow_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_state_transitions_ibfk_2` FOREIGN KEY (`transition_id`) REFERENCES `workflow_transitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
