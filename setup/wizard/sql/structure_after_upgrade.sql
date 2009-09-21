-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 08, 2009 at 04:08 PM
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

--
-- Dumping data for table `active_sessions`
--

INSERT INTO `active_sessions` (`id`, `user_id`, `session_id`, `lastused`, `ip`, `apptype`) VALUES
(1, 1, 'kl699bngr3h2bg3p8etu8q7gh7', '2009-09-08 16:06:02', '127.0.0.1', 'webapp'),
(2, 1, 'kl699bngr3h2bg3p8etu8q7gh7', '2009-09-08 16:06:02', '127.0.0.1', 'webapp');

--
-- Dumping data for table `archive_restoration_request`
--


--
-- Dumping data for table `archiving_settings`
--


--
-- Dumping data for table `archiving_type_lookup`
--

INSERT INTO `archiving_type_lookup` (`id`, `name`) VALUES
(1, 'Date'),
(2, 'Utilisation');

--
-- Dumping data for table `authentication_sources`
--


--
-- Dumping data for table `baobab_keys`
--


--
-- Dumping data for table `baobab_scan`
--

INSERT INTO `baobab_scan` (`checkdate`, `verify`) VALUES
('1981-01-01 00:00:00', 0);

--
-- Dumping data for table `baobab_user_keys`
--


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

--
-- Dumping data for table `comment_searchable_text`
--


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
(137, 'e_signatures', 'Set Time Interval for Administrative Electronic Signature', 'Sets the time-interval (in seconds) before re-authentication is required in the administrative section', 'adminSignatureTime', 'default', '600', 'numeric_string', '', 1);

--
-- Dumping data for table `custom_sequences`
--


--
-- Dumping data for table `dashlet_disables`
--


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

--
-- Dumping data for table `discussion_comments`
--


--
-- Dumping data for table `discussion_threads`
--


--
-- Dumping data for table `documents`
--


--
-- Dumping data for table `document_alerts`
--


--
-- Dumping data for table `document_alerts_users`
--


--
-- Dumping data for table `document_archiving_link`
--


--
-- Dumping data for table `document_content_version`
--


--
-- Dumping data for table `document_fields`
--

INSERT INTO `document_fields` (`id`, `name`, `data_type`, `is_generic`, `has_lookup`, `has_lookuptree`, `parent_fieldset`, `is_mandatory`, `description`, `position`, `is_html`, `max_length`) VALUES
(2, 'Tag', 'STRING', 0, 0, 0, 2, 0, 'Tag Words', 0, NULL, NULL),
(3, 'Document Author', 'STRING', 0, 0, 0, 3, 0, 'Please add a document author', 0, NULL, NULL),
(4, 'Category', 'STRING', 0, 1, 0, 3, 0, 'Please select a category', 1, NULL, NULL),
(5, 'Media Type', 'STRING', 0, 1, 0, 3, 0, 'Please select a media type', 2, NULL, NULL);

--
-- Dumping data for table `document_fields_link`
--


--
-- Dumping data for table `document_incomplete`
--


--
-- Dumping data for table `document_link`
--


--
-- Dumping data for table `document_link_types`
--

INSERT INTO `document_link_types` (`id`, `name`, `reverse_name`, `description`) VALUES
(-1, 'depended on', 'was depended on by', 'Depends relationship whereby one documents depends on another''s creation to go through approval'),
(0, 'Default', 'Default (reverse)', 'Default link type'),
(3, 'Attachment', '', 'Document Attachment'),
(4, 'Reference', '', 'Document Reference'),
(5, 'Copy', '', 'Document Copy');

--
-- Dumping data for table `document_metadata_version`
--


--
-- Dumping data for table `document_role_allocations`
--


--
-- Dumping data for table `document_searchable_text`
--


--
-- Dumping data for table `document_subscriptions`
--


--
-- Dumping data for table `document_tags`
--


--
-- Dumping data for table `document_text`
--


--
-- Dumping data for table `document_transactions`
--


--
-- Dumping data for table `document_transaction_text`
--


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

--
-- Dumping data for table `document_types_lookup`
--

INSERT INTO `document_types_lookup` (`id`, `name`, `disabled`, `scheme`, `regen_on_checkin`) VALUES
(1, 'Default', 0, '<DOCID>', 0);

--
-- Dumping data for table `document_type_alerts`
--


--
-- Dumping data for table `document_type_fieldsets_link`
--


--
-- Dumping data for table `document_type_fields_link`
--


--
-- Dumping data for table `download_files`
--


--
-- Dumping data for table `download_queue`
--


--
-- Dumping data for table `fieldsets`
--

INSERT INTO `fieldsets` (`id`, `name`, `namespace`, `mandatory`, `is_conditional`, `master_field`, `is_generic`, `is_complex`, `is_complete`, `is_system`, `description`, `disabled`) VALUES
(2, 'Tag Cloud', 'tagcloud', 0, 0, NULL, 1, 0, 0, 0, 'Tag Cloud', 0),
(3, 'General information', 'generalinformation', 0, 0, NULL, 1, 0, 0, 0, 'General document information', 0);

--
-- Dumping data for table `field_behaviours`
--


--
-- Dumping data for table `field_behaviour_options`
--


--
-- Dumping data for table `field_orders`
--


--
-- Dumping data for table `field_value_instances`
--


--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `name`, `description`, `parent_id`, `creator_id`, `created`, `modified_user_id`, `modified`, `is_public`, `parent_folder_ids`, `full_path`, `permission_object_id`, `permission_lookup_id`, `restrict_document_types`, `owner_id`, `linked_folder_id`) VALUES
(1, 'Root Folder', 'Root Folder', NULL, 1, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', 0, NULL, NULL, 1, 5, 0, 1, NULL),
(2, 'DroppedDocuments', 'DroppedDocuments', 1, 1, '2009-09-08 16:06:02', 1, '2009-09-08 16:06:02', 0, '1', 'DroppedDocuments', 2, 8, 0, 1, NULL),
(3, 'admin', 'admin', 2, 1, '2009-09-08 16:06:03', 1, '2009-09-08 16:06:03', 0, '1,2', 'DroppedDocuments/admin', 3, 10, 0, 1, NULL);

--
-- Dumping data for table `folders_users_roles_link`
--


--
-- Dumping data for table `folder_descendants`
--


--
-- Dumping data for table `folder_doctypes_link`
--

INSERT INTO `folder_doctypes_link` (`id`, `folder_id`, `document_type_id`) VALUES
(1, 1, 1);

--
-- Dumping data for table `folder_searchable_text`
--

INSERT INTO `folder_searchable_text` (`folder_id`, `folder_text`) VALUES
(1, 'Root Folder');

--
-- Dumping data for table `folder_subscriptions`
--


--
-- Dumping data for table `folder_transactions`
--

INSERT INTO `folder_transactions` (`id`, `folder_id`, `user_id`, `datetime`, `ip`, `comment`, `transaction_namespace`, `session_id`, `admin_mode`) VALUES
(1, 2, 1, '2009-09-08 16:06:02', '127.0.0.1', 'Folder created', 'ktcore.transactions.create', 2, 0),
(2, 3, 1, '2009-09-08 16:06:03', '127.0.0.1', 'Folder created', 'ktcore.transactions.create', 2, 0);

--
-- Dumping data for table `folder_workflow_map`
--


--
-- Dumping data for table `groups_groups_link`
--


--
-- Dumping data for table `groups_lookup`
--

INSERT INTO `groups_lookup` (`id`, `name`, `is_sys_admin`, `is_unit_admin`, `unit_id`, `authentication_details_s2`, `authentication_details_s1`, `authentication_source_id`) VALUES
(1, 'System Administrators', 1, 0, NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `help_replacement`
--


--
-- Dumping data for table `index_files`
--


--
-- Dumping data for table `interceptor_instances`
--

INSERT INTO `interceptor_instances` (`id`, `name`, `interceptor_namespace`, `config`) VALUES
(1, 'Password Reset Interceptor', 'password.reset.login.interceptor', ''),
(2, 'Password Reset Interceptor', 'password.reset.login.interceptor', ''),
(3, 'Password Reset Interceptor', 'password.reset.login.interceptor', '');

--
-- Dumping data for table `links`
--


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

--
-- Dumping data for table `metadata_lookup_tree`
--


--
-- Dumping data for table `mime_documents`
--


--
-- Dumping data for table `mime_document_mapping`
--


--
-- Dumping data for table `mime_extractors`
--


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

--
-- Dumping data for table `news`
--


--
-- Dumping data for table `notifications`
--


--
-- Dumping data for table `organisations_lookup`
--

INSERT INTO `organisations_lookup` (`id`, `name`) VALUES
(1, 'Default Organisation');

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

--
-- Dumping data for table `permission_descriptors`
--

INSERT INTO `permission_descriptors` (`id`, `descriptor`, `descriptor_text`) VALUES
(1, 'd41d8cd98f00b204e9800998ecf8427e', ''),
(2, 'a689e7c4dc953de8d93b1ed4843b2dfe', 'group(1)'),
(3, '426b9d5f4837e3407e43f96722cbe308', 'group(1)role(5)'),
(4, '69956554f671b2f1819ff895730ceff9', 'user(1)'),
(5, 'bca11de862fdb4a335a3001ea80d9b61', 'group(1)user(1)');

--
-- Dumping data for table `permission_descriptor_groups`
--

INSERT INTO `permission_descriptor_groups` (`descriptor_id`, `group_id`) VALUES
(2, 1),
(3, 1),
(5, 1);

--
-- Dumping data for table `permission_descriptor_roles`
--

INSERT INTO `permission_descriptor_roles` (`descriptor_id`, `role_id`) VALUES
(3, 5);

--
-- Dumping data for table `permission_descriptor_users`
--

INSERT INTO `permission_descriptor_users` (`descriptor_id`, `user_id`) VALUES
(4, 1),
(5, 1);

--
-- Dumping data for table `permission_dynamic_assignments`
--


--
-- Dumping data for table `permission_dynamic_conditions`
--


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

--
-- Dumping data for table `permission_objects`
--

INSERT INTO `permission_objects` (`id`) VALUES
(1),
(2),
(3);

--
-- Dumping data for table `plugins`
--

INSERT INTO `plugins` (`id`, `namespace`, `path`, `version`, `disabled`, `data`, `unavailable`, `friendly_name`, `orderby`) VALUES
(1, 'ktcore.tagcloud.plugin', 'plugins/tagcloud/TagCloudPlugin.php', 1, 0, NULL, 0, 'Tag Cloud Plugin', 0),
(2, 'ktcore.rss.plugin', 'plugins/rssplugin/RSSPlugin.php', 0, 0, NULL, 0, 'RSS Plugin', 0),
(3, 'ktcore.language.plugin', 'plugins/ktcore/KTCoreLanguagePlugin.php', 0, 0, NULL, 0, 'Core Language Support', -75),
(4, 'ktcore.plugin', 'plugins/ktcore/KTCorePlugin.php', 0, 0, NULL, 0, 'Core Application Functionality', -25),
(5, 'ktstandard.ldapauthentication.plugin', 'plugins/ktstandard/KTLDAPAuthenticationPlugin.php', 0, 0, NULL, 0, 'LDAP Authentication Plugin', 0),
(6, 'ktstandard.pdf.plugin', 'plugins/ktstandard/PDFGeneratorPlugin.php', 0, 0, NULL, 0, 'PDF Generator Plugin', 0),
(7, 'ktstandard.bulkexport.plugin', 'plugins/ktstandard/KTBulkExportPlugin.php', 0, 0, NULL, 0, 'Bulk Export Plugin', 0),
(8, 'ktstandard.immutableaction.plugin', 'plugins/ktstandard/ImmutableActionPlugin.php', 0, 0, NULL, 0, 'Immutable action plugin', 0),
(9, 'ktstandard.subscriptions.plugin', 'plugins/ktstandard/KTSubscriptions.php', 0, 0, NULL, 0, 'Subscription Plugin', 0),
(10, 'ktstandard.discussion.plugin', 'plugins/ktstandard/KTDiscussion.php', 0, 0, NULL, 0, 'Document Discussions Plugin', 0),
(11, 'ktstandard.email.plugin', 'plugins/ktstandard/KTEmail.php', 0, 0, NULL, 0, 'Email Plugin', 0),
(12, 'ktstandard.indexer.plugin', 'plugins/ktstandard/KTIndexer.php', 0, 0, NULL, 0, 'Full-text Content Indexing', 0),
(13, 'ktstandard.documentlinks.plugin', 'plugins/ktstandard/KTDocumentLinks.php', 0, 0, NULL, 0, 'Inter-document linking', 0),
(14, 'ktstandard.workflowassociation.plugin', 'plugins/ktstandard/KTWorkflowAssociation.php', 0, 0, NULL, 0, 'Workflow Association Plugin', 0),
(15, 'ktstandard.workflowassociation.documenttype.plugin', 'plugins/ktstandard/workflow/TypeAssociator.php', 0, 0, NULL, 0, 'Workflow allocation by document type', 0),
(16, 'ktstandard.workflowassociation.folder.plugin', 'plugins/ktstandard/workflow/FolderAssociator.php', 0, 0, NULL, 0, 'Workflow allocation by location', 0),
(17, 'ktstandard.disclaimers.plugin', 'plugins/ktstandard/KTDisclaimers.php', 0, 0, NULL, 0, 'Disclaimers Plugin', 0),
(18, 'nbm.browseable.plugin', 'plugins/browseabledashlet/BrowseableDashletPlugin.php', 0, 0, NULL, 0, 'Orphaned Folders Plugin', 0),
(19, 'ktstandard.ktwebdavdashlet.plugin', 'plugins/ktstandard/KTWebDAVDashletPlugin.php', 0, 0, NULL, 0, 'WebDAV Dashlet Plugin', 0),
(20, 'ktcore.housekeeper.plugin', 'plugins/housekeeper/HouseKeeperPlugin.php', 0, 0, NULL, 0, 'Housekeeper', 0),
(21, 'ktstandard.preview.plugin', 'plugins/ktstandard/documentpreview/documentPreviewPlugin.php', 0, 0, NULL, 0, 'Property Preview Plugin', 0),
(22, 'ktlive.mydropdocuments.plugin', 'plugins/MyDropDocumentsPlugin/MyDropDocumentsPlugin.php', 0, 0, NULL, 0, 'Drop Documents Plugin', 0),
(23, 'ktcore.i18.de_DE.plugin', 'plugins/i18n/german/GermanPlugin.php', 0, 0, NULL, 0, 'German translation plugin', -50),
(24, 'ktcore.i18.ja_JA.plugin', 'plugins/commercial-plugins/i18n/japanese/JapanesePlugin.php', 0, 0, NULL, 0, 'Commercial Japanese translation plugin', -50),
(25, 'ktcore.i18.it_IT.plugin', 'plugins/i18n/italian/ItalianPlugin.php', 0, 0, NULL, 0, 'Italian translation plugin', -50),
(26, 'ktcore.i18.fr_FR.plugin', 'plugins/i18n/french/FrenchPlugin.php', 0, 0, NULL, 0, 'French translation', -50),
(27, 'ktdms.wintools', 'plugins/commercial-plugins/wintools/BaobabPlugin.php', 2, 0, NULL, 0, 'Windows Tools:  Key Management', -20),
(28, 'password.reset.plugin', 'plugins/passwordResetPlugin/passwordResetPlugin.php', 0, 1, NULL, 0, 'Password Reset Plugin', 0),
(29, 'pdf.converter.processor.plugin', 'plugins/pdfConverter/pdfConverterPlugin.php', 0, 0, NULL, 0, 'Document PDF Converter', 0),
(30, 'thumbnails.generator.processor.plugin', 'plugins/thumbnails/thumbnailsPlugin.php', 0, 0, NULL, 0, 'Thumbnail Generator', 0),
(31, 'office.addin.plugin', 'plugins/commercial-plugins/officeaddin/officeaddinPlugin.php', 0, 0, NULL, 0, 'Office Add-In Plugin', 0),
(32, 'client.tools.plugin', 'plugins/commercial-plugins/clienttools/clientToolsPlugin.php', 0, 0, NULL, 0, 'Client Tools Plugin', 0),
(33, 'custom-numbering.plugin', 'plugins/commercial-plugins/custom-numbering/CustomNumberingPlugin.php', 0, 0, NULL, 1, 'Custom Numbering', 0),
(34, 'document.alerts.plugin', 'plugins/commercial-plugins/alerts/alertPlugin.php', 1, 0, NULL, 0, 'Document Alerts Plugin', 0),
(35, 'guid.inserter.plugin', 'plugins/commercial-plugins/guidInserter/guidInserterPlugin.php', 0, 1, NULL, 0, 'Document GUID Inserter (Experimental)', 0),
(36, 'shortcuts.plugin', 'plugins/commercial-plugins/shortcuts/ShortcutsPlugin.php', 0, 0, NULL, 0, 'Shortcuts', 0),
(37, 'electronic.signatures.plugin', 'plugins/commercial-plugins/electronic-signatures/KTElectronicSignaturesPlugin.php', 0, 0, NULL, 0, 'Electronic Signatures', 0),
(38, 'ktextra.conditionalmetadata.plugin', 'plugins/commercial-plugins/conditional-metadata/ConditionalMetadataPlugin.php', 0, 0, NULL, 0, 'Conditional Metadata Plugin', 0),
(39, 'document.comparison.plugin', 'plugins/commercial-plugins/documentcomparison/DocumentComparisonPlugin.php', 0, 0, NULL, 1, 'Document Comparison Plugin', 0),
(40, 'bd.Quicklinks.plugin', 'plugins/commercial-plugins/network/quicklinks/QuicklinksPlugin.php', 2, 0, NULL, 0, 'Quicklinks Plugin', 0),
(41, 'ktnetwork.GoToDocumentId.plugin', 'plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php', 0, 0, NULL, 0, 'Document Jump Dashlet', 0),
(42, 'ktprofessional.reporting.plugin', 'plugins/commercial-plugins/professional-reporting/ProfessionalReportingPlugin.php', 0, 0, NULL, 0, 'Professional Reporting', 0),
(43, 'instaview.processor.plugin', 'plugins/commercial-plugins/instaView/instaViewPlugin.php', 0, 0, NULL, 0, 'InstaView Document Viewer', 0),
(44, 'ktnetwork.inlineview.plugin', 'plugins/commercial-plugins/network/inlineview/InlineViewPlugin.php', 0, 0, NULL, 0, 'Inline View of Documents', 0),
(45, 'ktnetwork.TopDownloads.plugin', 'plugins/commercial-plugins/network/topdownloads/TopDownloadsPlugin.php', 0, 0, NULL, 0, 'Top Downloads for the last Week', 0),
(46, 'brad.UserHistory.plugin', 'plugins/commercial-plugins/network/userhistory/UserHistoryPlugin.php', 0, 0, NULL, 0, 'User History', 0),
(47, 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'plugins/commercial-plugins/network/extendedtransactioninfo/ExtendedTransactionInfoPlugin.php', 0, 0, NULL, 0, 'Extended Transaction Information', 0);

--
-- Dumping data for table `plugin_helper`
--

INSERT INTO `plugin_helper` (`id`, `namespace`, `plugin`, `classname`, `pathname`, `object`, `classtype`, `viewtype`) VALUES
(1, 'ktcore.language.plugin', 'ktcore.language.plugin', 'KTCoreLanguagePlugin', 'plugins/ktcore/KTCoreLanguagePlugin.php', 'KTCoreLanguagePlugin|ktcore.language.plugin|plugins/ktcore/KTCoreLanguagePlugin.php', 'plugin', 'general'),
(2, 'ktcore.plugin', 'ktcore.plugin', 'KTCorePlugin', 'plugins/ktcore/KTCorePlugin.php', 'KTCorePlugin|ktcore.plugin|plugins/ktcore/KTCorePlugin.php', 'plugin', 'general'),
(3, 'ktstandard.subscriptions.plugin', 'ktstandard.subscriptions.plugin', 'KTSubscriptionPlugin', 'plugins/ktstandard/KTSubscriptions.php', 'KTSubscriptionPlugin|ktstandard.subscriptions.plugin|plugins/ktstandard/KTSubscriptions.php', 'plugin', 'general'),
(4, 'ktstandard.discussion.plugin', 'ktstandard.discussion.plugin', 'KTDiscussionPlugin', 'plugins/ktstandard/KTDiscussion.php', 'KTDiscussionPlugin|ktstandard.discussion.plugin|plugins/ktstandard/KTDiscussion.php', 'plugin', 'general'),
(5, 'ktstandard.email.plugin', 'ktstandard.email.plugin', 'KTEmailPlugin', 'plugins/ktstandard/KTEmail.php', 'KTEmailPlugin|ktstandard.email.plugin|plugins/ktstandard/KTEmail.php', 'plugin', 'general'),
(6, 'ktstandard.indexer.plugin', 'ktstandard.indexer.plugin', 'KTIndexerPlugin', 'plugins/ktstandard/KTIndexer.php', 'KTIndexerPlugin|ktstandard.indexer.plugin|plugins/ktstandard/KTIndexer.php', 'plugin', 'general'),
(7, 'ktstandard.documentlinks.plugin', 'ktstandard.documentlinks.plugin', 'KTDocumentLinks', 'plugins/ktstandard/KTDocumentLinks.php', 'KTDocumentLinks|ktstandard.documentlinks.plugin|plugins/ktstandard/KTDocumentLinks.php', 'plugin', 'general'),
(8, 'ktstandard.workflowassociation.plugin', 'ktstandard.workflowassociation.plugin', 'KTWorkflowAssociationPlugin', 'plugins/ktstandard/KTWorkflowAssociation.php', 'KTWorkflowAssociationPlugin|ktstandard.workflowassociation.plugin|plugins/ktstandard/KTWorkflowAssociation.php', 'plugin', 'general'),
(9, 'ktstandard.workflowassociation.documenttype.plugin', 'ktstandard.workflowassociation.documenttype.plugin', 'KTDocTypeWorkflowAssociationPlugin', 'plugins/ktstandard/workflow/TypeAssociator.php', 'KTDocTypeWorkflowAssociationPlugin|ktstandard.workflowassociation.documenttype.plugin|plugins/ktstandard/workflow/TypeAssociator.php', 'plugin', 'general'),
(10, 'ktstandard.workflowassociation.folder.plugin', 'ktstandard.workflowassociation.folder.plugin', 'KTFolderWorkflowAssociationPlugin', 'plugins/ktstandard/workflow/FolderAssociator.php', 'KTFolderWorkflowAssociationPlugin|ktstandard.workflowassociation.folder.plugin|plugins/ktstandard/workflow/FolderAssociator.php', 'plugin', 'general'),
(11, 'ktstandard.disclaimers.plugin', 'ktstandard.disclaimers.plugin', 'KTDisclaimersPlugin', 'plugins/ktstandard/KTDisclaimers.php', 'KTDisclaimersPlugin|ktstandard.disclaimers.plugin|plugins/ktstandard/KTDisclaimers.php', 'plugin', 'general'),
(12, 'ktstandard.bulkexport.plugin', 'ktstandard.bulkexport.plugin', 'KTBulkExportPlugin', 'plugins/ktstandard/KTBulkExportPlugin.php', 'KTBulkExportPlugin|ktstandard.bulkexport.plugin|plugins/ktstandard/KTBulkExportPlugin.php', 'plugin', 'general'),
(13, 'ktstandard.ktwebdavdashlet.plugin', 'ktstandard.ktwebdavdashlet.plugin', 'KTWebDAVDashletPlugin', 'plugins/ktstandard/KTWebDAVDashletPlugin.php', 'KTWebDAVDashletPlugin|ktstandard.ktwebdavdashlet.plugin|plugins/ktstandard/KTWebDAVDashletPlugin.php', 'plugin', 'general'),
(14, 'ktstandard.immutableaction.plugin', 'ktstandard.immutableaction.plugin', 'KTImmutableActionPlugin', 'plugins/ktstandard/ImmutableActionPlugin.php', 'KTImmutableActionPlugin|ktstandard.immutableaction.plugin|plugins/ktstandard/ImmutableActionPlugin.php', 'plugin', 'general'),
(15, 'ktstandard.preview.plugin', 'ktstandard.preview.plugin', 'DocumentPreviewPlugin', 'plugins/ktstandard/documentpreview/documentPreviewPlugin.php', 'DocumentPreviewPlugin|ktstandard.preview.plugin|plugins/ktstandard/documentpreview/documentPreviewPlugin.php', 'plugin', 'general'),
(16, 'ktstandard.pdf.plugin', 'ktstandard.pdf.plugin', 'PDFGeneratorPlugin', 'plugins/ktstandard/PDFGeneratorPlugin.php', 'PDFGeneratorPlugin|ktstandard.pdf.plugin|plugins/ktstandard/PDFGeneratorPlugin.php', 'plugin', 'general'),
(17, 'ktstandard.ldapauthentication.plugin', 'ktstandard.ldapauthentication.plugin', 'KTLDAPAuthenticationPlugin', 'plugins/ktstandard/KTLDAPAuthenticationPlugin.php', 'KTLDAPAuthenticationPlugin|ktstandard.ldapauthentication.plugin|plugins/ktstandard/KTLDAPAuthenticationPlugin.php', 'plugin', 'general'),
(18, 'ktcore.i18.de_DE.plugin', 'ktcore.i18.de_DE.plugin', 'GermanPlugin', 'plugins/i18n/german/GermanPlugin.php', 'GermanPlugin|ktcore.i18.de_DE.plugin|plugins/i18n/german/GermanPlugin.php', 'plugin', 'general'),
(19, 'ktcore.i18.ja_JA.plugin', 'ktcore.i18.ja_JA.plugin', 'JapanesePlugin', 'plugins/commercial-plugins/i18n/japanese/JapanesePlugin.php', 'JapanesePlugin|ktcore.i18.ja_JA.plugin|plugins/commercial-plugins/i18n/japanese/JapanesePlugin.php', 'plugin', 'general'),
(20, 'ktcore.i18.it_IT.plugin', 'ktcore.i18.it_IT.plugin', 'ItalianPlugin', 'plugins/i18n/italian/ItalianPlugin.php', 'ItalianPlugin|ktcore.i18.it_IT.plugin|plugins/i18n/italian/ItalianPlugin.php', 'plugin', 'general'),
(21, 'ktcore.i18.fr_FR.plugin', 'ktcore.i18.fr_FR.plugin', 'FrenchPlugin', 'plugins/i18n/french/FrenchPlugin.php', 'FrenchPlugin|ktcore.i18.fr_FR.plugin|plugins/i18n/french/FrenchPlugin.php', 'plugin', 'general'),
(22, 'ktcore.housekeeper.plugin', 'ktcore.housekeeper.plugin', 'HouseKeeperPlugin', 'plugins/housekeeper/HouseKeeperPlugin.php', 'HouseKeeperPlugin|ktcore.housekeeper.plugin|plugins/housekeeper/HouseKeeperPlugin.php', 'plugin', 'general'),
(23, 'password.reset.plugin', 'password.reset.plugin', 'PasswordResetPlugin', 'plugins/passwordResetPlugin/passwordResetPlugin.php', 'PasswordResetPlugin|password.reset.plugin|plugins/passwordResetPlugin/passwordResetPlugin.php', 'plugin', 'general'),
(24, 'pdf.converter.processor.plugin', 'pdf.converter.processor.plugin', 'pdfConverterPlugin', 'plugins/pdfConverter/pdfConverterPlugin.php', 'pdfConverterPlugin|pdf.converter.processor.plugin|plugins/pdfConverter/pdfConverterPlugin.php', 'plugin', 'general'),
(25, 'ktcore.rss.plugin', 'ktcore.rss.plugin', 'RSSPlugin', 'plugins/rssplugin/RSSPlugin.php', 'RSSPlugin|ktcore.rss.plugin|plugins/rssplugin/RSSPlugin.php', 'plugin', 'general'),
(26, 'nbm.browseable.plugin', 'nbm.browseable.plugin', 'BrowseableDashletPlugin', 'plugins/browseabledashlet/BrowseableDashletPlugin.php', 'BrowseableDashletPlugin|nbm.browseable.plugin|plugins/browseabledashlet/BrowseableDashletPlugin.php', 'plugin', 'general'),
(27, 'thumbnails.generator.processor.plugin', 'thumbnails.generator.processor.plugin', 'thumbnailsPlugin', 'plugins/thumbnails/thumbnailsPlugin.php', 'thumbnailsPlugin|thumbnails.generator.processor.plugin|plugins/thumbnails/thumbnailsPlugin.php', 'plugin', 'general'),
(28, 'ktcore.tagcloud.plugin', 'ktcore.tagcloud.plugin', 'TagCloudPlugin', 'plugins/tagcloud/TagCloudPlugin.php', 'TagCloudPlugin|ktcore.tagcloud.plugin|plugins/tagcloud/TagCloudPlugin.php', 'plugin', 'general'),
(29, 'office.addin.plugin', 'office.addin.plugin', 'OfficeAddinPlugin', 'plugins/commercial-plugins/officeaddin/officeaddinPlugin.php', 'OfficeAddinPlugin|office.addin.plugin|plugins/commercial-plugins/officeaddin/officeaddinPlugin.php', 'plugin', 'general'),
(30, 'client.tools.plugin', 'client.tools.plugin', 'clientToolsPlugin', 'plugins/commercial-plugins/clienttools/clientToolsPlugin.php', 'clientToolsPlugin|client.tools.plugin|plugins/commercial-plugins/clienttools/clientToolsPlugin.php', 'plugin', 'general'),
(31, 'custom-numbering.plugin', 'custom-numbering.plugin', 'CustomNumberingPlugin', 'plugins/commercial-plugins/custom-numbering/CustomNumberingPlugin.php', 'CustomNumberingPlugin|custom-numbering.plugin|plugins/commercial-plugins/custom-numbering/CustomNumberingPlugin.php', 'plugin', 'general'),
(32, 'ktdms.wintools', 'ktdms.wintools', 'BaobabPlugin', 'plugins/commercial-plugins/wintools/BaobabPlugin.php', 'BaobabPlugin|ktdms.wintools|plugins/commercial-plugins/wintools/BaobabPlugin.php', 'plugin', 'general'),
(33, 'document.alerts.plugin', 'document.alerts.plugin', 'AlertPlugin', 'plugins/commercial-plugins/alerts/alertPlugin.php', 'AlertPlugin|document.alerts.plugin|plugins/commercial-plugins/alerts/alertPlugin.php', 'plugin', 'general'),
(34, 'guid.inserter.plugin', 'guid.inserter.plugin', 'GuidInserterPlugin', 'plugins/commercial-plugins/guidInserter/guidInserterPlugin.php', 'GuidInserterPlugin|guid.inserter.plugin|plugins/commercial-plugins/guidInserter/guidInserterPlugin.php', 'plugin', 'general'),
(35, 'shortcuts.plugin', 'shortcuts.plugin', 'ShortcutsPlugin', 'plugins/commercial-plugins/shortcuts/ShortcutsPlugin.php', 'ShortcutsPlugin|shortcuts.plugin|plugins/commercial-plugins/shortcuts/ShortcutsPlugin.php', 'plugin', 'general'),
(36, 'electronic.signatures.plugin', 'electronic.signatures.plugin', 'KTElectronicSignaturesPlugin', 'plugins/commercial-plugins/electronic-signatures/KTElectronicSignaturesPlugin.php', 'KTElectronicSignaturesPlugin|electronic.signatures.plugin|plugins/commercial-plugins/electronic-signatures/KTElectronicSignaturesPlugin.php', 'plugin', 'general'),
(37, 'ktextra.conditionalmetadata.plugin', 'ktextra.conditionalmetadata.plugin', 'ConditionalMetadataPlugin', 'plugins/commercial-plugins/conditional-metadata/ConditionalMetadataPlugin.php', 'ConditionalMetadataPlugin|ktextra.conditionalmetadata.plugin|plugins/commercial-plugins/conditional-metadata/ConditionalMetadataPlugin.php', 'plugin', 'general'),
(38, 'document.comparison.plugin', 'document.comparison.plugin', 'DocumentComparisonPlugin', 'plugins/commercial-plugins/documentcomparison/DocumentComparisonPlugin.php', 'DocumentComparisonPlugin|document.comparison.plugin|plugins/commercial-plugins/documentcomparison/DocumentComparisonPlugin.php', 'plugin', 'general'),
(39, 'bd.Quicklinks.plugin', 'bd.Quicklinks.plugin', 'QuicklinksPlugin', 'plugins/commercial-plugins/network/quicklinks/QuicklinksPlugin.php', 'QuicklinksPlugin|bd.Quicklinks.plugin|plugins/commercial-plugins/network/quicklinks/QuicklinksPlugin.php', 'plugin', 'general'),
(40, 'ktnetwork.GoToDocumentId.plugin', 'ktnetwork.GoToDocumentId.plugin', 'GoToDocumentIdPlugin', 'plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php', 'GoToDocumentIdPlugin|ktnetwork.GoToDocumentId.plugin|plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php', 'plugin', 'general'),
(41, 'ktprofessional.reporting.plugin', 'ktprofessional.reporting.plugin', 'KTProfessionalReportingPlugin', 'plugins/commercial-plugins/professional-reporting/ProfessionalReportingPlugin.php', 'KTProfessionalReportingPlugin|ktprofessional.reporting.plugin|plugins/commercial-plugins/professional-reporting/ProfessionalReportingPlugin.php', 'plugin', 'general'),
(42, 'instaview.processor.plugin', 'instaview.processor.plugin', 'instaViewPlugin', 'plugins/commercial-plugins/instaView/instaViewPlugin.php', 'instaViewPlugin|instaview.processor.plugin|plugins/commercial-plugins/instaView/instaViewPlugin.php', 'plugin', 'general'),
(43, 'ktnetwork.inlineview.plugin', 'ktnetwork.inlineview.plugin', 'InlineViewPlugin', 'plugins/commercial-plugins/network/inlineview/InlineViewPlugin.php', 'InlineViewPlugin|ktnetwork.inlineview.plugin|plugins/commercial-plugins/network/inlineview/InlineViewPlugin.php', 'plugin', 'general'),
(44, 'ktnetwork.TopDownloads.plugin', 'ktnetwork.TopDownloads.plugin', 'TopDownloadsPlugin', 'plugins/commercial-plugins/network/topdownloads/TopDownloadsPlugin.php', 'TopDownloadsPlugin|ktnetwork.TopDownloads.plugin|plugins/commercial-plugins/network/topdownloads/TopDownloadsPlugin.php', 'plugin', 'general'),
(45, 'brad.UserHistory.plugin', 'brad.UserHistory.plugin', 'UserHistoryPlugin', 'plugins/commercial-plugins/network/userhistory/UserHistoryPlugin.php', 'UserHistoryPlugin|brad.UserHistory.plugin|plugins/commercial-plugins/network/userhistory/UserHistoryPlugin.php', 'plugin', 'general'),
(46, 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'ExtendedTransactionInfoPlugin', 'plugins/commercial-plugins/network/extendedtransactioninfo/ExtendedTransactionInfoPlugin.php', 'ExtendedTransactionInfoPlugin|ktnetwork.ExtendedDocumentTransactionInfo.plugin|plugins/commercial-plugins/network/extendedtransactioninfo/ExtendedTransactionInfoPlugin.php', 'plugin', 'general'),
(47, 'ktlive.mydropdocuments.plugin', 'ktlive.mydropdocuments.plugin', 'MyDropDocumentsPlugin', 'plugins/MyDropDocumentsPlugin/MyDropDocumentsPlugin.php', 'MyDropDocumentsPlugin|ktlive.mydropdocuments.plugin|plugins/MyDropDocumentsPlugin/MyDropDocumentsPlugin.php', 'plugin', 'general'),
(48, 'knowledgeTree', 'ktcore.language.plugin', 'knowledgeTree', 'i18n', 'knowledgeTree|i18n', 'i18n', 'general'),
(49, 'knowledgeTree/en', 'ktcore.language.plugin', 'knowledgeTree', 'default', 'knowledgeTree|en|default', 'i18nlang', 'general'),
(50, 'en', 'ktcore.language.plugin', NULL, NULL, 'en|English (United States)', 'language', 'general'),
(51, 'en', 'ktcore.language.plugin', NULL, '', 'ktcore|en|kthelp/ktcore/EN', 'help_language', 'general'),
(52, 'knowledgeTree/de_DE', 'ktcore.i18.de_DE.plugin', 'knowledgeTree', 'plugins/i18n/german/translations', 'knowledgeTree|de_DE|plugins/i18n/german/translations', 'i18nlang', 'general'),
(53, 'de_DE', 'ktcore.i18.de_DE.plugin', NULL, NULL, 'de_DE|Deutsch (Deutschland)', 'language', 'general'),
(54, 'de_DE', 'ktcore.i18.de_DE.plugin', NULL, '', 'ktcore|de_DE|plugins/i18n/german/help/ktcore', 'help_language', 'general'),
(55, 'knowledgeTree/ja_JA', 'ktcore.i18.ja_JA.plugin', 'knowledgeTree', '', 'knowledgeTree|ja_JA|', 'i18nlang', 'general'),
(56, 'ja_JA', 'ktcore.i18.ja_JA.plugin', NULL, NULL, 'ja_JA|Japanese (Japan)', 'language', 'general'),
(57, 'ja_JA', 'ktcore.i18.ja_JA.plugin', NULL, '', 'ktcore|ja_JA|', 'help_language', 'general'),
(58, 'knowledgeTree/it_IT', 'ktcore.i18.it_IT.plugin', 'knowledgeTree', 'plugins/i18n/italian/translations', 'knowledgeTree|it_IT|plugins/i18n/italian/translations', 'i18nlang', 'general'),
(59, 'it_IT', 'ktcore.i18.it_IT.plugin', NULL, NULL, 'it_IT|Italiano (Italia)', 'language', 'general'),
(60, 'it_IT', 'ktcore.i18.it_IT.plugin', NULL, '', 'ktcore|it_IT|plugins/i18n/italian/help/ktcore', 'help_language', 'general'),
(61, 'knowledgeTree/fr_FR', 'ktcore.i18.fr_FR.plugin', 'knowledgeTree', 'plugins/i18n/french/translations', 'knowledgeTree|fr_FR|plugins/i18n/french/translations', 'i18nlang', 'general'),
(62, 'fr_FR', 'ktcore.i18.fr_FR.plugin', NULL, NULL, 'fr_FR|French (France)', 'language', 'general'),
(63, 'fr_FR', 'ktcore.i18.fr_FR.plugin', NULL, '', 'ktcore|fr_FR|plugins/i18n/french/help/ktcore', 'help_language', 'general'),
(64, 'ktcore.actions.document.displaydetails', 'ktcore.plugin', 'KTDocumentDetailsAction', 'plugins/ktcore/KTDocumentActions.php', 'documentinfo|KTDocumentDetailsAction|ktcore.actions.document.displaydetails|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(65, 'ktcore.actions.document.view', 'ktcore.plugin', 'KTDocumentViewAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentViewAction|ktcore.actions.document.view|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(66, 'ktcore.actions.document.ownershipchange', 'ktcore.plugin', 'KTOwnershipChangeAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTOwnershipChangeAction|ktcore.actions.document.ownershipchange|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(67, 'ktcore.actions.document.checkout', 'ktcore.plugin', 'KTDocumentCheckOutAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentCheckOutAction|ktcore.actions.document.checkout|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(68, 'ktcore.actions.document.cancelcheckout', 'ktcore.plugin', 'KTDocumentCancelCheckOutAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentCancelCheckOutAction|ktcore.actions.document.cancelcheckout|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(69, 'ktcore.actions.document.checkin', 'ktcore.plugin', 'KTDocumentCheckInAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentCheckInAction|ktcore.actions.document.checkin|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(70, 'ktcore.actions.document.edit', 'ktcore.plugin', 'KTDocumentEditAction', 'plugins/ktcore/document/edit.php', 'documentaction|KTDocumentEditAction|ktcore.actions.document.edit|plugins/ktcore/document/edit.php|ktcore.plugin', 'action', 'general'),
(71, 'ktcore.actions.document.delete', 'ktcore.plugin', 'KTDocumentDeleteAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentDeleteAction|ktcore.actions.document.delete|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(72, 'ktcore.actions.document.move', 'ktcore.plugin', 'KTDocumentMoveAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentMoveAction|ktcore.actions.document.move|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(73, 'ktcore.actions.document.copy', 'ktcore.plugin', 'KTDocumentCopyAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentCopyAction|ktcore.actions.document.copy|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(74, 'ktcore.actions.document.rename', 'ktcore.plugin', 'KTDocumentRenameAction', 'plugins/ktcore/document/Rename.php', 'documentaction|KTDocumentRenameAction|ktcore.actions.document.rename|plugins/ktcore/document/Rename.php|ktcore.plugin', 'action', 'general'),
(75, 'ktcore.search2.index.action', 'ktcore.plugin', 'DocumentIndexAction', 'plugins/search2/DocumentIndexAction.php', 'documentaction|DocumentIndexAction|ktcore.search2.index.action|plugins/search2/DocumentIndexAction.php|ktcore.plugin', 'action', 'general'),
(76, 'ktcore.actions.document.transactionhistory', 'ktcore.plugin', 'KTDocumentTransactionHistoryAction', 'plugins/ktcore/KTDocumentActions.php', 'documentinfo|KTDocumentTransactionHistoryAction|ktcore.actions.document.transactionhistory|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(77, 'ktcore.actions.document.versionhistory', 'ktcore.plugin', 'KTDocumentVersionHistoryAction', 'plugins/ktcore/KTDocumentActions.php', 'documentinfo|KTDocumentVersionHistoryAction|ktcore.actions.document.versionhistory|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(78, 'ktcore.actions.document.archive', 'ktcore.plugin', 'KTDocumentArchiveAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentArchiveAction|ktcore.actions.document.archive|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(79, 'ktcore.actions.document.workflow', 'ktcore.plugin', 'KTDocumentWorkflowAction', 'plugins/ktcore/KTDocumentActions.php', 'documentaction|KTDocumentWorkflowAction|ktcore.actions.document.workflow|plugins/ktcore/KTDocumentActions.php|ktcore.plugin', 'action', 'general'),
(80, 'ktcore.actions.folder.view', 'ktcore.plugin', 'KTFolderViewAction', 'plugins/ktcore/KTFolderActions.php', 'folderinfo|KTFolderViewAction|ktcore.actions.folder.view|plugins/ktcore/KTFolderActions.php|ktcore.plugin', 'action', 'general'),
(81, 'ktcore.actions.folder.addDocument', 'ktcore.plugin', 'KTFolderAddDocumentAction', 'plugins/ktcore/folder/addDocument.php', 'folderaction|KTFolderAddDocumentAction|ktcore.actions.folder.addDocument|plugins/ktcore/folder/addDocument.php|ktcore.plugin', 'action', 'general'),
(82, 'ktcore.actions.folder.addFolder', 'ktcore.plugin', 'KTFolderAddFolderAction', 'plugins/ktcore/KTFolderActions.php', 'folderaction|KTFolderAddFolderAction|ktcore.actions.folder.addFolder|plugins/ktcore/KTFolderActions.php|ktcore.plugin', 'action', 'general'),
(83, 'ktcore.actions.folder.rename', 'ktcore.plugin', 'KTFolderRenameAction', 'plugins/ktcore/folder/Rename.php', 'folderaction|KTFolderRenameAction|ktcore.actions.folder.rename|plugins/ktcore/folder/Rename.php|ktcore.plugin', 'action', 'general'),
(84, 'ktcore.actions.folder.permissions', 'ktcore.plugin', 'KTFolderPermissionsAction', 'plugins/ktcore/folder/Permissions.php', 'folderaction|KTFolderPermissionsAction|ktcore.actions.folder.permissions|plugins/ktcore/folder/Permissions.php|ktcore.plugin', 'action', 'general'),
(85, 'ktcore.actions.folder.bulkImport', 'ktcore.plugin', 'KTBulkImportFolderAction', 'plugins/ktcore/folder/BulkImport.php', 'folderaction|KTBulkImportFolderAction|ktcore.actions.folder.bulkImport|plugins/ktcore/folder/BulkImport.php|ktcore.plugin', 'action', 'general'),
(86, 'ktcore.actions.folder.bulkUpload', 'ktcore.plugin', 'KTBulkUploadFolderAction', 'plugins/ktcore/folder/BulkUpload.php', 'folderaction|KTBulkUploadFolderAction|ktcore.actions.folder.bulkUpload|plugins/ktcore/folder/BulkUpload.php|ktcore.plugin', 'action', 'general'),
(87, 'ktcore.search2.index.folder.action', 'ktcore.plugin', 'FolderIndexAction', 'plugins/search2/FolderIndexAction.php', 'folderaction|FolderIndexAction|ktcore.search2.index.folder.action|plugins/search2/FolderIndexAction.php|ktcore.plugin', 'action', 'general'),
(88, 'ktcore.actions.folder.transactions', 'ktcore.plugin', 'KTFolderTransactionsAction', 'plugins/ktcore/folder/Transactions.php', 'folderinfo|KTFolderTransactionsAction|ktcore.actions.folder.transactions|plugins/ktcore/folder/Transactions.php|ktcore.plugin', 'action', 'general'),
(89, 'ktcore.actions.document.assist', 'ktcore.plugin', 'KTDocumentAssistAction', 'plugins/ktcore/KTAssist.php', 'documentaction|KTDocumentAssistAction|ktcore.actions.document.assist|plugins/ktcore/KTAssist.php|ktcore.plugin', 'action', 'general'),
(90, 'ktcore.viewlets.document.workflow', 'ktcore.plugin', 'KTWorkflowViewlet', 'plugins/ktcore/KTDocumentViewlets.php', 'documentviewlet|KTWorkflowViewlet|ktcore.viewlets.document.workflow|plugins/ktcore/KTDocumentViewlets.php|ktcore.plugin', 'action', 'general'),
(91, 'ktcore/assist', 'ktcore.plugin', 'KTAssistNotification', 'plugins/ktcore/KTAssist.php', 'ktcore/assist|KTAssistNotification|plugins/ktcore/KTAssist.php', 'notification_handler', 'general'),
(92, 'ktcore/subscriptions', 'ktcore.plugin', 'KTSubscriptionNotification', 'lib/dashboard/Notification.inc.php', 'ktcore/subscriptions|KTSubscriptionNotification|lib/dashboard/Notification.inc.php', 'notification_handler', 'general'),
(93, 'ktcore/workflow', 'ktcore.plugin', 'KTWorkflowNotification', 'lib/dashboard/Notification.inc.php', 'ktcore/workflow|KTWorkflowNotification|lib/dashboard/Notification.inc.php', 'notification_handler', 'general'),
(94, 'ktcore.actions.document.permissions', 'ktcore.plugin', 'KTDocumentPermissionsAction', 'plugins/ktcore/KTPermissions.php', 'documentinfo|KTDocumentPermissionsAction|ktcore.actions.document.permissions|plugins/ktcore/KTPermissions.php|ktcore.plugin', 'action', 'general'),
(95, 'ktcore.actions.folder.roles', 'ktcore.plugin', 'KTRoleAllocationPlugin', 'plugins/ktcore/KTPermissions.php', 'folderaction|KTRoleAllocationPlugin|ktcore.actions.folder.roles|plugins/ktcore/KTPermissions.php|ktcore.plugin', 'action', 'general'),
(96, 'ktcore.actions.document.roles', 'ktcore.plugin', 'KTDocumentRolesAction', 'plugins/ktcore/KTPermissions.php', 'documentinfo|KTDocumentRolesAction|ktcore.actions.document.roles|plugins/ktcore/KTPermissions.php|ktcore.plugin', 'action', 'general'),
(97, 'ktcore.actions.bulk.delete', 'ktcore.plugin', 'KTBulkDeleteAction', 'plugins/ktcore/KTBulkActions.php', 'bulkaction|KTBulkDeleteAction|ktcore.actions.bulk.delete|plugins/ktcore/KTBulkActions.php|ktcore.plugin', 'action', 'general'),
(98, 'ktcore.actions.bulk.move', 'ktcore.plugin', 'KTBulkMoveAction', 'plugins/ktcore/KTBulkActions.php', 'bulkaction|KTBulkMoveAction|ktcore.actions.bulk.move|plugins/ktcore/KTBulkActions.php|ktcore.plugin', 'action', 'general'),
(99, 'ktcore.actions.bulk.copy', 'ktcore.plugin', 'KTBulkCopyAction', 'plugins/ktcore/KTBulkActions.php', 'bulkaction|KTBulkCopyAction|ktcore.actions.bulk.copy|plugins/ktcore/KTBulkActions.php|ktcore.plugin', 'action', 'general'),
(100, 'ktcore.actions.bulk.archive', 'ktcore.plugin', 'KTBulkArchiveAction', 'plugins/ktcore/KTBulkActions.php', 'bulkaction|KTBulkArchiveAction|ktcore.actions.bulk.archive|plugins/ktcore/KTBulkActions.php|ktcore.plugin', 'action', 'general'),
(101, 'ktcore.actions.bulk.export', 'ktcore.plugin', 'KTBrowseBulkExportAction', 'plugins/ktcore/KTBulkActions.php', 'bulkaction|KTBrowseBulkExportAction|ktcore.actions.bulk.export|plugins/ktcore/KTBulkActions.php|ktcore.plugin', 'action', 'general'),
(102, 'ktcore.actions.bulk.checkout', 'ktcore.plugin', 'KTBrowseBulkCheckoutAction', 'plugins/ktcore/KTBulkActions.php', 'bulkaction|KTBrowseBulkCheckoutAction|ktcore.actions.bulk.checkout|plugins/ktcore/KTBulkActions.php|ktcore.plugin', 'action', 'general'),
(103, 'ktcore.dashlet.info', 'ktcore.plugin', 'KTInfoDashlet', 'plugins/ktcore/KTDashlets.php', 'KTInfoDashlet|ktcore.dashlet.info|plugins/ktcore/KTDashlets.php|ktcore.plugin', 'dashlet', 'dashboard'),
(104, 'ktcore.dashlet.notifications', 'ktcore.plugin', 'KTNotificationDashlet', 'plugins/ktcore/KTDashlets.php', 'KTNotificationDashlet|ktcore.dashlet.notifications|plugins/ktcore/KTDashlets.php|ktcore.plugin', 'dashlet', 'dashboard'),
(105, 'ktcore.dashlet.checkout', 'ktcore.plugin', 'KTCheckoutDashlet', 'plugins/ktcore/KTDashlets.php', 'KTCheckoutDashlet|ktcore.dashlet.checkout|plugins/ktcore/KTDashlets.php|ktcore.plugin', 'dashlet', 'dashboard'),
(106, 'ktcore.dashlet.mail_server', 'ktcore.plugin', 'KTMailServerDashlet', 'plugins/ktcore/KTDashlets.php', 'KTMailServerDashlet|ktcore.dashlet.mail_server|plugins/ktcore/KTDashlets.php|ktcore.plugin', 'dashlet', 'dashboard'),
(107, 'ktcore.dashlet.lucene_migration', 'ktcore.plugin', 'LuceneMigrationDashlet', 'plugins/search2/MigrationDashlet.php', 'LuceneMigrationDashlet|ktcore.dashlet.lucene_migration|plugins/search2/MigrationDashlet.php|ktcore.plugin', 'dashlet', 'dashboard'),
(108, 'ktcore.schedulerdashlet.plugin', 'ktcore.plugin', 'schedulerDashlet', 'plugins/ktcore/scheduler/schedulerDashlet.php', 'schedulerDashlet|ktcore.schedulerdashlet.plugin|plugins/ktcore/scheduler/schedulerDashlet.php|ktcore.plugin', 'dashlet', 'dashboard'),
(109, 'misc/scheduler', 'ktcore.plugin', 'manageSchedulerDispatcher', 'plugins/ktcore/scheduler/taskScheduler.php', 'scheduler|manageSchedulerDispatcher|misc|Manage Task Scheduler|Manage the task scheduler|plugins/ktcore/scheduler/taskScheduler.php||ktcore.plugin', 'admin_page', 'general'),
(110, 'principals/authentication', 'ktcore.plugin', 'KTAuthenticationAdminPage', 'plugins/ktcore/authentication/authenticationadminpage.inc.php', 'authentication|KTAuthenticationAdminPage|principals|Authentication|By default, KnowledgeTree controls its own users and groups and stores all information about them inside the database. In many situations, an organisation will already have a list of users and groups, and needs to use that existing information to allow access to the DMS.   These <strong>Authentication Sources</strong> allow the system administrator to  specify additional sources of authentication data.|plugins/ktcore/authentication/authenticationadminpage.inc.php||ktcore.plugin', 'admin_page', 'general'),
(111, 'ktcore.search2.portlet', 'ktcore.plugin', 'Search2Portlet', 'plugins/search2/Search2Portlet.php', 'a:2:{i:0;s:6:"browse";i:1;s:9:"dashboard";}|Search2Portlet|ktcore.search2.portlet|plugins/search2/Search2Portlet.php|ktcore.plugin', 'portlet', 'general'),
(112, 'ktcore.portlets.admin_mode', 'ktcore.plugin', 'KTAdminModePortlet', 'plugins/ktcore/KTPortlets.php', 'a:1:{i:0;s:6:"browse";}|KTAdminModePortlet|ktcore.portlets.admin_mode|plugins/ktcore/KTPortlets.php|ktcore.plugin', 'portlet', 'general'),
(113, 'ktcore.portlets.browsemodes', 'ktcore.plugin', 'KTBrowseModePortlet', 'plugins/ktcore/KTPortlets.php', 'a:1:{i:0;s:6:"browse";}|KTBrowseModePortlet|ktcore.portlets.browsemodes|plugins/ktcore/KTPortlets.php|ktcore.plugin', 'portlet', 'general'),
(114, 'ktcore.portlets.adminnavigation', 'ktcore.plugin', 'KTAdminSectionNavigation', 'plugins/ktcore/KTPortlets.php', 'a:1:{i:0;s:14:"administration";}|KTAdminSectionNavigation|ktcore.portlets.adminnavigation|plugins/ktcore/KTPortlets.php|ktcore.plugin', 'portlet', 'general'),
(115, 'ktcore.columns.title', 'ktcore.plugin', 'AdvancedTitleColumn', 'plugins/ktcore/KTColumns.inc.php', 'Title|ktcore.columns.title|AdvancedTitleColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(116, 'ktcore.columns.selection', 'ktcore.plugin', 'AdvancedSelectionColumn', 'plugins/ktcore/KTColumns.inc.php', 'Selection|ktcore.columns.selection|AdvancedSelectionColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(117, 'ktcore.columns.singleselection', 'ktcore.plugin', 'AdvancedSingleSelectionColumn', 'plugins/ktcore/KTColumns.inc.php', 'Single Selection|ktcore.columns.singleselection|AdvancedSingleSelectionColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(118, 'ktcore.columns.workflow_state', 'ktcore.plugin', 'AdvancedWorkflowColumn', 'plugins/ktcore/KTColumns.inc.php', 'Workflow State|ktcore.columns.workflow_state|AdvancedWorkflowColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(119, 'ktcore.columns.checkedout_by', 'ktcore.plugin', 'CheckedOutByColumn', 'plugins/ktcore/KTColumns.inc.php', 'Checked Out By|ktcore.columns.checkedout_by|CheckedOutByColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(120, 'ktcore.columns.creationdate', 'ktcore.plugin', 'CreationDateColumn', 'plugins/ktcore/KTColumns.inc.php', 'Creation Date|ktcore.columns.creationdate|CreationDateColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(121, 'ktcore.columns.modificationdate', 'ktcore.plugin', 'ModificationDateColumn', 'plugins/ktcore/KTColumns.inc.php', 'Modification Date|ktcore.columns.modificationdate|ModificationDateColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(122, 'ktcore.columns.creator', 'ktcore.plugin', 'CreatorColumn', 'plugins/ktcore/KTColumns.inc.php', 'Creator|ktcore.columns.creator|CreatorColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(123, 'ktcore.columns.download', 'ktcore.plugin', 'AdvancedDownloadColumn', 'plugins/ktcore/KTColumns.inc.php', 'Download File|ktcore.columns.download|AdvancedDownloadColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(124, 'ktcore.columns.docid', 'ktcore.plugin', 'DocumentIDColumn', 'plugins/ktcore/KTColumns.inc.php', 'Document ID|ktcore.columns.docid|DocumentIDColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(125, 'ktcore.columns.containing_folder', 'ktcore.plugin', 'ContainingFolderColumn', 'plugins/ktcore/KTColumns.inc.php', 'Open Containing Folder|ktcore.columns.containing_folder|ContainingFolderColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(126, 'ktcore.columns.document_type', 'ktcore.plugin', 'DocumentTypeColumn', 'plugins/ktcore/KTColumns.inc.php', 'Document Type|ktcore.columns.document_type|DocumentTypeColumn|plugins/ktcore/KTColumns.inc.php', 'column', 'general'),
(127, 'ktcore.views.browse', 'ktcore.plugin', '', '', 'Browse Documents|ktcore.views.browse', 'view', 'general'),
(128, 'ktcore.views.search', 'ktcore.plugin', '', '', 'Search|ktcore.views.search', 'view', 'general'),
(129, 'ktcore.workflowtriggers.permissionguard', 'ktcore.plugin', 'PermissionGuardTrigger', 'plugins/ktcore/KTWorkflowTriggers.inc.php', 'ktcore.workflowtriggers.permissionguard|PermissionGuardTrigger|plugins/ktcore/KTWorkflowTriggers.inc.php', 'workflow_trigger', 'general'),
(130, 'ktcore.workflowtriggers.roleguard', 'ktcore.plugin', 'RoleGuardTrigger', 'plugins/ktcore/KTWorkflowTriggers.inc.php', 'ktcore.workflowtriggers.roleguard|RoleGuardTrigger|plugins/ktcore/KTWorkflowTriggers.inc.php', 'workflow_trigger', 'general'),
(131, 'ktcore.workflowtriggers.groupguard', 'ktcore.plugin', 'GroupGuardTrigger', 'plugins/ktcore/KTWorkflowTriggers.inc.php', 'ktcore.workflowtriggers.groupguard|GroupGuardTrigger|plugins/ktcore/KTWorkflowTriggers.inc.php', 'workflow_trigger', 'general'),
(132, 'ktcore.workflowtriggers.conditionguard', 'ktcore.plugin', 'ConditionGuardTrigger', 'plugins/ktcore/KTWorkflowTriggers.inc.php', 'ktcore.workflowtriggers.conditionguard|ConditionGuardTrigger|plugins/ktcore/KTWorkflowTriggers.inc.php', 'workflow_trigger', 'general'),
(133, 'ktcore.workflowtriggers.checkoutguard', 'ktcore.plugin', 'CheckoutGuardTrigger', 'plugins/ktcore/KTWorkflowTriggers.inc.php', 'ktcore.workflowtriggers.checkoutguard|CheckoutGuardTrigger|plugins/ktcore/KTWorkflowTriggers.inc.php', 'workflow_trigger', 'general'),
(134, 'ktcore.workflowtriggers.copyaction', 'ktcore.plugin', 'CopyActionTrigger', 'plugins/ktcore/KTWorkflowTriggers.inc.php', 'ktcore.workflowtriggers.copyaction|CopyActionTrigger|plugins/ktcore/KTWorkflowTriggers.inc.php', 'workflow_trigger', 'general'),
(135, 'ktcore.workflowtriggers.moveaction', 'ktcore.plugin', 'MoveActionTrigger', 'plugins/ktcore/KTWorkflowTriggers.inc.php', 'ktcore.workflowtriggers.moveaction|MoveActionTrigger|plugins/ktcore/KTWorkflowTriggers.inc.php', 'workflow_trigger', 'general'),
(136, 'ktcore.search2.savedsearch.subscription.edit', 'ktcore.plugin', 'SavedSearchSubscriptionTrigger', 'plugins/search2/Search2Triggers.php', 'edit|postValidate|SavedSearchSubscriptionTrigger|ktcore.search2.savedsearch.subscription.edit|plugins/search2/Search2Triggers.php|ktcore.plugin', 'trigger', 'general'),
(137, 'ktcore.search2.savedsearch.subscription.add', 'ktcore.plugin', 'SavedSearchSubscriptionTrigger', 'plugins/search2/Search2Triggers.php', 'add|postValidate|SavedSearchSubscriptionTrigger|ktcore.search2.savedsearch.subscription.add|plugins/search2/Search2Triggers.php|ktcore.plugin', 'trigger', 'general'),
(138, 'ktcore.search2.savedsearch.subscription.discussion', 'ktcore.plugin', 'SavedSearchSubscriptionTrigger', 'plugins/search2/Search2Triggers.php', 'discussion|postValidate|SavedSearchSubscriptionTrigger|ktcore.search2.savedsearch.subscription.discussion|plugins/search2/Search2Triggers.php|ktcore.plugin', 'trigger', 'general'),
(139, 'ktcore.triggers.tagcloud.add', 'ktcore.plugin', 'KTAddDocumentTrigger', 'plugins/tagcloud/TagCloudTriggers.php', 'add|postValidate|KTAddDocumentTrigger|ktcore.triggers.tagcloud.add|plugins/tagcloud/TagCloudTriggers.php|ktcore.plugin', 'trigger', 'general'),
(140, 'ktcore.triggers.tagcloud.edit', 'ktcore.plugin', 'KTEditDocumentTrigger', 'plugins/tagcloud/TagCloudTriggers.php', 'edit|postValidate|KTEditDocumentTrigger|ktcore.triggers.tagcloud.edit|plugins/tagcloud/TagCloudTriggers.php|ktcore.plugin', 'trigger', 'general'),
(141, 'ktcore.widgets.info', 'ktcore.plugin', 'KTCoreInfoWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreInfoWidget|ktcore.widgets.info|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(142, 'ktcore.widgets.hidden', 'ktcore.plugin', 'KTCoreHiddenWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreHiddenWidget|ktcore.widgets.hidden|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(143, 'ktcore.widgets.string', 'ktcore.plugin', 'KTCoreStringWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreStringWidget|ktcore.widgets.string|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(144, 'ktcore.widgets.selection', 'ktcore.plugin', 'KTCoreSelectionWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreSelectionWidget|ktcore.widgets.selection|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(145, 'ktcore.widgets.entityselection', 'ktcore.plugin', 'KTCoreEntitySelectionWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreEntitySelectionWidget|ktcore.widgets.entityselection|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(146, 'ktcore.widgets.boolean', 'ktcore.plugin', 'KTCoreBooleanWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreBooleanWidget|ktcore.widgets.boolean|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(147, 'ktcore.widgets.password', 'ktcore.plugin', 'KTCorePasswordWidget', 'plugins/ktcore/KTWidgets.php', 'KTCorePasswordWidget|ktcore.widgets.password|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(148, 'ktcore.widgets.text', 'ktcore.plugin', 'KTCoreTextWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreTextWidget|ktcore.widgets.text|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(149, 'ktcore.widgets.reason', 'ktcore.plugin', 'KTCoreReasonWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreReasonWidget|ktcore.widgets.reason|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(150, 'ktcore.widgets.file', 'ktcore.plugin', 'KTCoreFileWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreFileWidget|ktcore.widgets.file|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(151, 'ktcore.widgets.fieldset', 'ktcore.plugin', 'KTCoreFieldsetWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreFieldsetWidget|ktcore.widgets.fieldset|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(152, 'ktcore.widgets.transparentfieldset', 'ktcore.plugin', 'KTCoreTransparentFieldsetWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreTransparentFieldsetWidget|ktcore.widgets.transparentfieldset|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(153, 'ktcore.widgets.collection', 'ktcore.plugin', 'KTCoreCollectionWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreCollectionWidget|ktcore.widgets.collection|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(154, 'ktcore.widgets.treemetadata', 'ktcore.plugin', 'KTCoreTreeMetadataWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreTreeMetadataWidget|ktcore.widgets.treemetadata|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(155, 'ktcore.widgets.descriptorselection', 'ktcore.plugin', 'KTDescriptorSelectionWidget', 'plugins/ktcore/KTWidgets.php', 'KTDescriptorSelectionWidget|ktcore.widgets.descriptorselection|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(156, 'ktcore.widgets.foldercollection', 'ktcore.plugin', 'KTCoreFolderCollectionWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreFolderCollectionWidget|ktcore.widgets.foldercollection|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(157, 'ktcore.widgets.textarea', 'ktcore.plugin', 'KTCoreTextAreaWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreTextAreaWidget|ktcore.widgets.textarea|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(158, 'ktcore.widgets.date', 'ktcore.plugin', 'KTCoreDateWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreDateWidget|ktcore.widgets.date|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(159, 'ktcore.widgets.conditionalselection', 'ktcore.plugin', 'KTCoreConditionalSelectionWidget', 'plugins/ktcore/KTWidgets.php', 'KTCoreConditionalSelectionWidget|ktcore.widgets.conditionalselection|plugins/ktcore/KTWidgets.php', 'widget', 'general'),
(160, 'ktcore.plugin/collection', 'ktcore.plugin', 'KTCoreCollectionPage', 'plugins/ktcore/KTWidgets.php', 'ktcore.plugin/collection|KTCoreCollectionPage|plugins/ktcore/KTWidgets.php|ktcore.plugin', 'page', 'general'),
(161, 'ktcore.plugin/notifications', 'ktcore.plugin', 'KTNotificationOverflowPage', 'plugins/ktcore/KTMiscPages.php', 'ktcore.plugin/notifications|KTNotificationOverflowPage|plugins/ktcore/KTMiscPages.php|ktcore.plugin', 'page', 'general'),
(162, 'ktcore.validators.string', 'ktcore.plugin', 'KTStringValidator', 'plugins/ktcore/KTValidators.php', 'KTStringValidator|ktcore.validators.string|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(163, 'ktcore.validators.illegal_char', 'ktcore.plugin', 'KTIllegalCharValidator', 'plugins/ktcore/KTValidators.php', 'KTIllegalCharValidator|ktcore.validators.illegal_char|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(164, 'ktcore.validators.entity', 'ktcore.plugin', 'KTEntityValidator', 'plugins/ktcore/KTValidators.php', 'KTEntityValidator|ktcore.validators.entity|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(165, 'ktcore.validators.required', 'ktcore.plugin', 'KTRequiredValidator', 'plugins/ktcore/KTValidators.php', 'KTRequiredValidator|ktcore.validators.required|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(166, 'ktcore.validators.emailaddress', 'ktcore.plugin', 'KTEmailValidator', 'plugins/ktcore/KTValidators.php', 'KTEmailValidator|ktcore.validators.emailaddress|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(167, 'ktcore.validators.boolean', 'ktcore.plugin', 'KTBooleanValidator', 'plugins/ktcore/KTValidators.php', 'KTBooleanValidator|ktcore.validators.boolean|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(168, 'ktcore.validators.password', 'ktcore.plugin', 'KTPasswordValidator', 'plugins/ktcore/KTValidators.php', 'KTPasswordValidator|ktcore.validators.password|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(169, 'ktcore.validators.membership', 'ktcore.plugin', 'KTMembershipValidator', 'plugins/ktcore/KTValidators.php', 'KTMembershipValidator|ktcore.validators.membership|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(170, 'ktcore.validators.fieldset', 'ktcore.plugin', 'KTFieldsetValidator', 'plugins/ktcore/KTValidators.php', 'KTFieldsetValidator|ktcore.validators.fieldset|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(171, 'ktcore.validators.file', 'ktcore.plugin', 'KTFileValidator', 'plugins/ktcore/KTValidators.php', 'KTFileValidator|ktcore.validators.file|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(172, 'ktcore.validators.requiredfile', 'ktcore.plugin', 'KTRequiredFileValidator', 'plugins/ktcore/KTValidators.php', 'KTRequiredFileValidator|ktcore.validators.requiredfile|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(173, 'ktcore.validators.fileillegalchar', 'ktcore.plugin', 'KTFileIllegalCharValidator', 'plugins/ktcore/KTValidators.php', 'KTFileIllegalCharValidator|ktcore.validators.fileillegalchar|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(174, 'ktcore.validators.array', 'ktcore.plugin', 'KTArrayValidator', 'plugins/ktcore/KTValidators.php', 'KTArrayValidator|ktcore.validators.array|plugins/ktcore/KTValidators.php', 'validator', 'general'),
(175, 'ktcore.criteria.name', 'ktcore.plugin', 'NameCriterion', 'lib/browse/Criteria.inc', 'NameCriterion|ktcore.criteria.name|lib/browse/Criteria.inc|', 'criterion', 'general'),
(176, 'ktcore.criteria.id', 'ktcore.plugin', 'IDCriterion', 'lib/browse/Criteria.inc', 'IDCriterion|ktcore.criteria.id|lib/browse/Criteria.inc|', 'criterion', 'general'),
(177, 'ktcore.criteria.title', 'ktcore.plugin', 'TitleCriterion', 'lib/browse/Criteria.inc', 'TitleCriterion|ktcore.criteria.title|lib/browse/Criteria.inc|', 'criterion', 'general'),
(178, 'ktcore.criteria.creator', 'ktcore.plugin', 'CreatorCriterion', 'lib/browse/Criteria.inc', 'CreatorCriterion|ktcore.criteria.creator|lib/browse/Criteria.inc|', 'criterion', 'general'),
(179, 'ktcore.criteria.datecreated', 'ktcore.plugin', 'DateCreatedCriterion', 'lib/browse/Criteria.inc', 'DateCreatedCriterion|ktcore.criteria.datecreated|lib/browse/Criteria.inc|', 'criterion', 'general'),
(180, 'ktcore.criteria.documenttype', 'ktcore.plugin', 'DocumentTypeCriterion', 'lib/browse/Criteria.inc', 'DocumentTypeCriterion|ktcore.criteria.documenttype|lib/browse/Criteria.inc|', 'criterion', 'general'),
(181, 'ktcore.criteria.datemodified', 'ktcore.plugin', 'DateModifiedCriterion', 'lib/browse/Criteria.inc', 'DateModifiedCriterion|ktcore.criteria.datemodified|lib/browse/Criteria.inc|', 'criterion', 'general'),
(182, 'ktcore.criteria.size', 'ktcore.plugin', 'SizeCriterion', 'lib/browse/Criteria.inc', 'SizeCriterion|ktcore.criteria.size|lib/browse/Criteria.inc|', 'criterion', 'general'),
(183, 'ktcore.criteria.workflowstate', 'ktcore.plugin', 'WorkflowStateCriterion', 'lib/browse/Criteria.inc', 'WorkflowStateCriterion|ktcore.criteria.workflowstate|lib/browse/Criteria.inc|', 'criterion', 'general'),
(184, 'ktcore.criteria.datecreateddelta', 'ktcore.plugin', 'DateCreatedDeltaCriterion', 'lib/browse/Criteria.inc', 'DateCreatedDeltaCriterion|ktcore.criteria.datecreateddelta|lib/browse/Criteria.inc|', 'criterion', 'general'),
(185, 'ktcore.criteria.datemodifieddelta', 'ktcore.plugin', 'DateModifiedDeltaCriterion', 'lib/browse/Criteria.inc', 'DateModifiedDeltaCriterion|ktcore.criteria.datemodifieddelta|lib/browse/Criteria.inc|', 'criterion', 'general'),
(186, 'ktcore.criteria.generalmetadata', 'ktcore.plugin', 'GeneralMetadataCriterion', 'lib/browse/Criteria.inc', 'GeneralMetadataCriterion|ktcore.criteria.generalmetadata|lib/browse/Criteria.inc|', 'criterion', 'general'),
(187, 'principals', 'ktcore.plugin', 'Users and Groups', 'principals', 'principals|Users and Groups|Control which users can log in, and are part of which groups and organisational units, from these management panels.', 'admin_category', 'general'),
(188, 'security', 'ktcore.plugin', 'Security Management', 'security', 'security|Security Management|Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System.', 'admin_category', 'general'),
(189, 'storage', 'ktcore.plugin', 'Document Storage', 'storage', 'storage|Document Storage|Manage checked-out, archived and deleted documents.', 'admin_category', 'general'),
(190, 'documents', 'ktcore.plugin', 'Document Metadata and Workflow Configuration', 'documents', 'documents|Document Metadata and Workflow Configuration|Configure the document metadata: Document Types, Document Fieldsets, Link Types and Workflows.', 'admin_category', 'general'),
(191, 'search', 'ktcore.plugin', 'Search and Indexing', 'search', 'search|Search and Indexing|Search and Indexing Settings', 'admin_category', 'general'),
(192, 'config', 'ktcore.plugin', 'System Configuration', 'config', 'config|System Configuration|System Configuration Settings', 'admin_category', 'general'),
(193, 'misc', 'ktcore.plugin', 'Miscellaneous', 'misc', 'misc|Miscellaneous|Various settings which do not fit into the other categories, including managing help and saved searches.', 'admin_category', 'general'),
(194, 'principals/users', 'ktcore.plugin', 'KTUserAdminDispatcher', 'plugins/ktcore/admin/userManagement.php', 'users|KTUserAdminDispatcher|principals|Manage Users|Add or remove users from the system.|plugins/ktcore/admin/userManagement.php||ktcore.plugin', 'admin_page', 'general'),
(195, 'principals/groups', 'ktcore.plugin', 'KTGroupAdminDispatcher', 'plugins/ktcore/admin/groupManagement.php', 'groups|KTGroupAdminDispatcher|principals|Manage Groups|Add or remove groups from the system.|plugins/ktcore/admin/groupManagement.php||ktcore.plugin', 'admin_page', 'general'),
(196, 'principals/units', 'ktcore.plugin', 'KTUnitAdminDispatcher', 'plugins/ktcore/admin/unitManagement.php', 'units|KTUnitAdminDispatcher|principals|Control Units|Specify which organisational units are available within the repository.|plugins/ktcore/admin/unitManagement.php||ktcore.plugin', 'admin_page', 'general'),
(197, 'security/permissions', 'ktcore.plugin', 'ManagePermissionsDispatcher', 'plugins/ktcore/admin/managePermissions.php', 'permissions|ManagePermissionsDispatcher|security|Permissions|Create or delete permissions.|plugins/ktcore/admin/managePermissions.php||ktcore.plugin', 'admin_page', 'general'),
(198, 'security/roles', 'ktcore.plugin', 'RoleAdminDispatcher', 'plugins/ktcore/admin/roleManagement.php', 'roles|RoleAdminDispatcher|security|Roles|Create or delete roles|plugins/ktcore/admin/roleManagement.php||ktcore.plugin', 'admin_page', 'general'),
(199, 'security/conditions', 'ktcore.plugin', 'KTConditionDispatcher', 'plugins/ktcore/admin/conditions.php', 'conditions|KTConditionDispatcher|security|Dynamic Conditions|Manage criteria which determine whether a user is permitted to perform a system action.|plugins/ktcore/admin/conditions.php||ktcore.plugin', 'admin_page', 'general'),
(200, 'documents/typemanagement', 'ktcore.plugin', 'KTDocumentTypeDispatcher', 'plugins/ktcore/admin/documentTypes.php', 'typemanagement|KTDocumentTypeDispatcher|documents|Document Types|Manage the different classes of document which can be added to the system.|plugins/ktcore/admin/documentTypes.php||ktcore.plugin', 'admin_page', 'general'),
(201, 'documents/fieldmanagement2', 'ktcore.plugin', 'KTDocumentFieldDispatcher', 'plugins/ktcore/admin/documentFieldsv2.php', 'fieldmanagement2|KTDocumentFieldDispatcher|documents|Document Fieldsets|Manage the different types of information that can be associated with classes of documents.|plugins/ktcore/admin/documentFieldsv2.php||ktcore.plugin', 'admin_page', 'general'),
(202, 'documents/emailtypemanagement', 'ktcore.plugin', 'KTEmailDocumentTypeDispatcher', '', 'emailtypemanagement|KTEmailDocumentTypeDispatcher|documents|Email Document Types|Manage the addition of Email document types to the system.|||ktcore.plugin', 'admin_page', 'general'),
(203, 'documents/workflows_2', 'ktcore.plugin', 'KTWorkflowAdminV2', 'plugins/ktcore/admin/workflowsv2.php', 'workflows_2|KTWorkflowAdminV2|documents|Workflows|Configure automated Workflows that map to document life-cycles.|plugins/ktcore/admin/workflowsv2.php||ktcore.plugin', 'admin_page', 'general'),
(204, 'storage/checkout', 'ktcore.plugin', 'KTCheckoutAdminDispatcher', 'plugins/ktcore/admin/documentCheckout.php', 'checkout|KTCheckoutAdminDispatcher|storage|Checked Out Document Control|Override the checked-out status of documents if a user has failed to do so.|plugins/ktcore/admin/documentCheckout.php||ktcore.plugin', 'admin_page', 'general'),
(205, 'storage/archived', 'ktcore.plugin', 'ArchivedDocumentsDispatcher', 'plugins/ktcore/admin/archivedDocuments.php', 'archived|ArchivedDocumentsDispatcher|storage|Archived Document Restoration|Restore old (archived) documents, usually at a user''s request.|plugins/ktcore/admin/archivedDocuments.php||ktcore.plugin', 'admin_page', 'general'),
(206, 'storage/expunge', 'ktcore.plugin', 'DeletedDocumentsDispatcher', 'plugins/ktcore/admin/deletedDocuments.php', 'expunge|DeletedDocumentsDispatcher|storage|Restore or Expunge Deleted Documents|Restore previously deleted documents, or permanently expunge them.|plugins/ktcore/admin/deletedDocuments.php||ktcore.plugin', 'admin_page', 'general');
INSERT INTO `plugin_helper` (`id`, `namespace`, `plugin`, `classname`, `pathname`, `object`, `classtype`, `viewtype`) VALUES
(207, 'search/managemimetypes', 'ktcore.plugin', 'ManageMimeTypesDispatcher', 'plugins/search2/reporting/ManageMimeTypes.php', 'managemimetypes|ManageMimeTypesDispatcher|search|Mime Types|This report lists all mime types and extensions that can be identified by KnowledgeTree.|plugins/search2/reporting/ManageMimeTypes.php||ktcore.plugin', 'admin_page', 'general'),
(208, 'search/extractorinfo', 'ktcore.plugin', 'ExtractorInfoDispatcher', 'plugins/search2/reporting/ExtractorInfo.php', 'extractorinfo|ExtractorInfoDispatcher|search|Extractor Information|This report lists the text extractors and their supported mime types.|plugins/search2/reporting/ExtractorInfo.php||ktcore.plugin', 'admin_page', 'general'),
(209, 'search/indexerrors', 'ktcore.plugin', 'IndexErrorsDispatcher', 'plugins/search2/reporting/IndexErrors.php', 'indexerrors|IndexErrorsDispatcher|search|Document Indexing Diagnostics|This report will help to diagnose problems with document indexing.|plugins/search2/reporting/IndexErrors.php||ktcore.plugin', 'admin_page', 'general'),
(210, 'search/pendingdocuments', 'ktcore.plugin', 'PendingDocumentsDispatcher', 'plugins/search2/reporting/PendingDocuments.php', 'pendingdocuments|PendingDocumentsDispatcher|search|Pending Documents Indexing Queue|This report lists documents that are waiting to be indexed.|plugins/search2/reporting/PendingDocuments.php||ktcore.plugin', 'admin_page', 'general'),
(211, 'search/reschedulealldocuments', 'ktcore.plugin', 'RescheduleDocumentsDispatcher', 'plugins/search2/reporting/RescheduleDocuments.php', 'reschedulealldocuments|RescheduleDocumentsDispatcher|search|Reschedule all documents|This function allows you to re-index your entire repository.|plugins/search2/reporting/RescheduleDocuments.php||ktcore.plugin', 'admin_page', 'general'),
(212, 'search/indexingstatus', 'ktcore.plugin', 'IndexingStatusDispatcher', 'plugins/search2/reporting/IndexingStatus.php', 'indexingstatus|IndexingStatusDispatcher|search|Document Indexer and External Resource Dependancy Status|This report will show the status of external dependencies and the document indexer.|plugins/search2/reporting/IndexingStatus.php||ktcore.plugin', 'admin_page', 'general'),
(213, 'search/lucenestatistics', 'ktcore.plugin', 'LuceneStatisticsDispatcher', 'plugins/search2/reporting/LuceneStatistics.php', 'lucenestatistics|LuceneStatisticsDispatcher|search|Document Indexer Statistics|This report will show the Lucene Document Indexing Statistics |plugins/search2/reporting/LuceneStatistics.php||ktcore.plugin', 'admin_page', 'general'),
(214, 'config/emailconfigpage', 'ktcore.plugin', 'EmailConfigPageDispatcher', 'plugins/ktcore/admin/configSettings.php', 'emailconfigpage|EmailConfigPageDispatcher|config|Email|Define the sending email server address, email password, email port, and user name, and view and modify policies for emailing documents and attachments from KnowledgeTree.|plugins/ktcore/admin/configSettings.php||ktcore.plugin', 'admin_page', 'general'),
(215, 'config/uiconfigpage', 'ktcore.plugin', 'UIConfigPageDispatcher', 'plugins/ktcore/admin/configSettings.php', 'uiconfigpage|UIConfigPageDispatcher|config|User Interface|View and modify settings on Browse View actions, OEM name, automatic refresh, search results restrictions, custom logo details, paths to dot binary, graphics, and log directory, and whether to enable/disable condensed UI, ''open'' from downloads, sort metadata, and skinning.|plugins/ktcore/admin/configSettings.php||ktcore.plugin', 'admin_page', 'general'),
(216, 'config/searchandindexingconfigpage', 'ktcore.plugin', 'SearchAndIndexingConfigPageDispatcher', 'plugins/ktcore/admin/configSettings.php', 'searchandindexingconfigpage|SearchAndIndexingConfigPageDispatcher|config|Search and Indexing|View and modify the number of documents indexed / migrated in a cron session, core indexing class, paths to the extractor hook, text extractors, indexing engine, Lucene indexes, and the Java Lucene URL. View and modify search date format, paths to search, indexing fields and libraries, results display format, and results per page.|plugins/ktcore/admin/configSettings.php||ktcore.plugin', 'admin_page', 'general'),
(217, 'config/clientconfigpage', 'ktcore.plugin', 'ClientSettingsConfigPageDispatcher', 'plugins/ktcore/admin/configSettings.php', 'clientconfigpage|ClientSettingsConfigPageDispatcher|config|Client Tools|View and change settings for the KnowledgeTree Tools Server, Client Tools Policies, WebDAV, and the OpenOffice.org service.|plugins/ktcore/admin/configSettings.php||ktcore.plugin', 'admin_page', 'general'),
(218, 'config/generalconfigpage', 'ktcore.plugin', 'GeneralConfigPageDispatcher', 'plugins/ktcore/admin/configSettings.php', 'generalconfigpage|GeneralConfigPageDispatcher|config|General Settings|View and modify settings for the KnowledgeTree cache, custom error message handling, Disk Usage threshold percentages, location of zip binary, paths to external binaries, general server configuration, LDAP authentication, session management, KnowledgeTree storage manager, miscellaneous tweaks, and whether to always display ''Your Checked-out Documents'' dashlet.|plugins/ktcore/admin/configSettings.php||ktcore.plugin', 'admin_page', 'general'),
(219, 'config/i18nconfigpage', 'ktcore.plugin', 'i18nConfigPageDispatcher', 'plugins/ktcore/admin/configSettings.php', 'i18nconfigpage|i18nConfigPageDispatcher|config|Internationalization|View and modify the default language.|plugins/ktcore/admin/configSettings.php||ktcore.plugin', 'admin_page', 'general'),
(220, 'config/securityconfigpage', 'ktcore.plugin', 'SecurityConfigPageDispatcher', 'plugins/ktcore/admin/configSettings.php', 'securityconfigpage|SecurityConfigPageDispatcher|config|Security|View and modify the security settings.|plugins/ktcore/admin/configSettings.php||ktcore.plugin', 'admin_page', 'general'),
(221, 'misc/helpmanagement', 'ktcore.plugin', 'ManageHelpDispatcher', 'plugins/ktcore/admin/manageHelp.php', 'helpmanagement|ManageHelpDispatcher|misc|Edit Help files|Change the help files that are displayed to users.|plugins/ktcore/admin/manageHelp.php||ktcore.plugin', 'admin_page', 'general'),
(222, 'misc/plugins', 'ktcore.plugin', 'KTPluginDispatcher', 'plugins/ktcore/admin/plugins.php', 'plugins|KTPluginDispatcher|misc|Manage plugins|Register new plugins, disable plugins, and so forth|plugins/ktcore/admin/plugins.php||ktcore.plugin', 'admin_page', 'general'),
(223, 'misc/techsupport', 'ktcore.plugin', 'KTSupportDispatcher', 'plugins/ktcore/admin/techsupport.php', 'techsupport|KTSupportDispatcher|misc|Support and System information|Information about this system and how to get support.|plugins/ktcore/admin/techsupport.php||ktcore.plugin', 'admin_page', 'general'),
(224, 'storage/cleanup', 'ktcore.plugin', 'ManageCleanupDispatcher', 'plugins/ktcore/admin/manageCleanup.php', 'cleanup|ManageCleanupDispatcher|storage|Verify Document Storage|Performs a check to see if the documents in your repositories all are stored on the back-end storage (usually on disk).|plugins/ktcore/admin/manageCleanup.php||ktcore.plugin', 'admin_page', 'general'),
(225, 'misc/views', 'ktcore.plugin', 'ManageViewDispatcher', 'plugins/ktcore/admin/manageViews.php', 'views|ManageViewDispatcher|misc|Manage views|Allows you to specify the columns that are to be used by a particular view (e.g. Browse documents, Search)|plugins/ktcore/admin/manageViews.php||ktcore.plugin', 'admin_page', 'general'),
(226, 'ktdms.wintools.naglet', 'ktdms.wintools', 'WinToolsNagDashlet', 'plugins/commercial-plugins/wintools/BaobabPlugin.php', 'WinToolsNagDashlet|ktdms.wintools.naglet|plugins/commercial-plugins/wintools/BaobabPlugin.php|ktdms.wintools', 'dashlet', 'dashboard'),
(227, 'ktcore.licensedashlet.plugin', 'ktdms.wintools', 'LicenseDashlet', 'plugins/commercial-plugins/wintools/licenseDashlet.php', 'LicenseDashlet|ktcore.licensedashlet.plugin|plugins/commercial-plugins/wintools/licenseDashlet.php|ktdms.wintools', 'dashlet', 'dashboard'),
(228, 'wintools', 'ktdms.wintools', 'License Administration', 'wintools', 'wintools|License Administration|Manage the keys you have purchased.', 'admin_category', 'general'),
(229, 'wintools/wintoolskeyadmin', 'ktdms.wintools', 'BaobabAdminKeysDispatcher', 'plugins/commercial-plugins/wintools/baobabKeyManagement.php', 'wintoolskeyadmin|BaobabAdminKeysDispatcher|wintools|Manage Keys|Add or remove keys, and review their expiry dates.|plugins/commercial-plugins/wintools/baobabKeyManagement.php||ktdms.wintools', 'admin_page', 'general'),
(230, 'ktdms.wintools', 'ktdms.wintools', 'ktdms.wintools', 'plugins/commercial-plugins/wintools/templates', 'wintools|plugins/commercial-plugins/wintools/templates', 'locations', 'general'),
(231, 'ktcore.portlets.subscription', 'ktstandard.subscriptions.plugin', 'KTSubscriptionPortlet', 'plugins/ktstandard/KTSubscriptions.php', 'browse|KTSubscriptionPortlet|ktcore.portlets.subscription|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'portlet', 'general'),
(232, 'ktstandard.subscription.documentsubscription', 'ktstandard.subscriptions.plugin', 'KTDocumentSubscriptionAction', 'plugins/ktstandard/KTSubscriptions.php', 'documentsubscriptionaction|KTDocumentSubscriptionAction|ktstandard.subscription.documentsubscription|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'action', 'general'),
(233, 'ktstandard.subscription.documentunsubscription', 'ktstandard.subscriptions.plugin', 'KTDocumentUnsubscriptionAction', 'plugins/ktstandard/KTSubscriptions.php', 'documentsubscriptionaction|KTDocumentUnsubscriptionAction|ktstandard.subscription.documentunsubscription|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'action', 'general'),
(234, 'ktstandard.triggers.subscription.checkout', 'ktstandard.subscriptions.plugin', 'KTCheckoutSubscriptionTrigger', 'plugins/ktstandard/KTSubscriptions.php', 'checkout|postValidate|KTCheckoutSubscriptionTrigger|ktstandard.triggers.subscription.checkout|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'trigger', 'general'),
(235, 'ktstandard.triggers.subscription.delete', 'ktstandard.subscriptions.plugin', 'KTDeleteSubscriptionTrigger', 'plugins/ktstandard/KTSubscriptions.php', 'delete|postValidate|KTDeleteSubscriptionTrigger|ktstandard.triggers.subscription.delete|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'trigger', 'general'),
(236, 'ktstandard.triggers.subscription.moveDocument', 'ktstandard.subscriptions.plugin', 'KTDocumentMoveSubscriptionTrigger', 'plugins/ktstandard/KTSubscriptions.php', 'moveDocument|postValidate|KTDocumentMoveSubscriptionTrigger|ktstandard.triggers.subscription.moveDocument|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'trigger', 'general'),
(237, 'ktstandard.triggers.subscription.archive', 'ktstandard.subscriptions.plugin', 'KTArchiveSubscriptionTrigger', 'plugins/ktstandard/KTSubscriptions.php', 'archive|postValidate|KTArchiveSubscriptionTrigger|ktstandard.triggers.subscription.archive|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'trigger', 'general'),
(238, 'ktstandard.subscription.foldersubscription', 'ktstandard.subscriptions.plugin', 'KTFolderSubscriptionAction', 'plugins/ktstandard/KTSubscriptions.php', 'foldersubscriptionaction|KTFolderSubscriptionAction|ktstandard.subscription.foldersubscription|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'action', 'general'),
(239, 'ktstandard.subscription.folderunsubscription', 'ktstandard.subscriptions.plugin', 'KTFolderUnsubscriptionAction', 'plugins/ktstandard/KTSubscriptions.php', 'foldersubscriptionaction|KTFolderUnsubscriptionAction|ktstandard.subscription.folderunsubscription|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'action', 'general'),
(240, 'ktstandard.subscriptions.plugin/manage', 'ktstandard.subscriptions.plugin', 'KTSubscriptionManagePage', 'plugins/ktstandard/KTSubscriptions.php', 'ktstandard.subscriptions.plugin/manage|KTSubscriptionManagePage|plugins/ktstandard/KTSubscriptions.php|ktstandard.subscriptions.plugin', 'page', 'general'),
(241, 'ktcore.actions.document.discussion', 'ktstandard.discussion.plugin', 'KTDocumentDiscussionAction', 'plugins/ktstandard/KTDiscussion.php', 'documentaction|KTDocumentDiscussionAction|ktcore.actions.document.discussion|plugins/ktstandard/KTDiscussion.php|ktstandard.discussion.plugin', 'action', 'general'),
(242, 'ktcore.actions.document.email', 'ktstandard.email.plugin', 'KTDocumentEmailAction', 'plugins/ktstandard/KTEmail.php', 'documentaction|KTDocumentEmailAction|ktcore.actions.document.email|plugins/ktstandard/KTEmail.php|ktstandard.email.plugin', 'action', 'general'),
(243, 'ktcore.actions.document.link', 'ktstandard.documentlinks.plugin', 'KTDocumentLinkAction', 'plugins/ktstandard/KTDocumentLinks.php', 'documentaction|KTDocumentLinkAction|ktcore.actions.document.link|plugins/ktstandard/KTDocumentLinks.php|ktstandard.documentlinks.plugin', 'action', 'general'),
(244, 'ktcore.viewlets.document.link', 'ktstandard.documentlinks.plugin', 'KTDocumentLinkViewlet', 'plugins/ktstandard/KTDocumentLinks.php', 'documentviewlet|KTDocumentLinkViewlet|ktcore.viewlets.document.link|plugins/ktstandard/KTDocumentLinks.php|ktstandard.documentlinks.plugin', 'action', 'general'),
(245, 'ktdocumentlinks.columns.title', 'ktstandard.documentlinks.plugin', 'KTDocumentLinkTitle', 'plugins/ktstandard/KTDocumentLinksColumns.php', 'Link Title|ktdocumentlinks.columns.title|KTDocumentLinkTitle|plugins/ktstandard/KTDocumentLinksColumns.php', 'column', 'general'),
(246, 'documents/linkmanagement', 'ktstandard.documentlinks.plugin', 'KTDocLinkAdminDispatcher', 'plugins/ktstandard/KTDocumentLinks.php', 'linkmanagement|KTDocLinkAdminDispatcher|documents|Link Type Management|Manage the different ways documents can be associated with one another.|plugins/ktstandard/KTDocumentLinks.php||ktstandard.documentlinks.plugin', 'admin_page', 'general'),
(247, 'ktstandard.triggers.workflowassociation.addDocument', 'ktstandard.workflowassociation.plugin', 'KTWADAddTrigger', 'plugins/ktstandard/KTWorkflowAssociation.php', 'add|postValidate|KTWADAddTrigger|ktstandard.triggers.workflowassociation.addDocument|plugins/ktstandard/KTWorkflowAssociation.php|ktstandard.workflowassociation.plugin', 'trigger', 'general'),
(248, 'ktstandard.triggers.workflowassociation.moveDocument', 'ktstandard.workflowassociation.plugin', 'KTWADMoveTrigger', 'plugins/ktstandard/KTWorkflowAssociation.php', 'moveDocument|postValidate|KTWADMoveTrigger|ktstandard.triggers.workflowassociation.moveDocument|plugins/ktstandard/KTWorkflowAssociation.php|ktstandard.workflowassociation.plugin', 'trigger', 'general'),
(249, 'ktstandard.triggers.workflowassociation.copyDocument', 'ktstandard.workflowassociation.plugin', 'KTWADCopyTrigger', 'plugins/ktstandard/KTWorkflowAssociation.php', 'copyDocument|postValidate|KTWADCopyTrigger|ktstandard.triggers.workflowassociation.copyDocument|plugins/ktstandard/KTWorkflowAssociation.php|ktstandard.workflowassociation.plugin', 'trigger', 'general'),
(250, 'ktstandard.triggers.workflowassociation.editDocument', 'ktstandard.workflowassociation.plugin', 'KTWADEditTrigger', 'plugins/ktstandard/KTWorkflowAssociation.php', 'edit|postValidate|KTWADEditTrigger|ktstandard.triggers.workflowassociation.editDocument|plugins/ktstandard/KTWorkflowAssociation.php|ktstandard.workflowassociation.plugin', 'trigger', 'general'),
(251, 'documents/workflow_allocation', 'ktstandard.workflowassociation.plugin', 'WorkflowAllocationSelection', 'plugins/ktstandard/workflow/adminpage.php', 'workflow_allocation|WorkflowAllocationSelection|documents|Automatic Workflow Assignments|Configure how documents are allocated to workflows.|plugins/ktstandard/workflow/adminpage.php||ktstandard.workflowassociation.plugin', 'admin_page', 'general'),
(252, 'ktstandard.triggers.workflowassociation.documenttype.handler', 'ktstandard.workflowassociation.documenttype.plugin', 'DocumentTypeWorkflowAssociator', 'plugins/ktstandard/workflow/TypeAssociator.php', 'workflow|objectModification|DocumentTypeWorkflowAssociator|ktstandard.triggers.workflowassociation.documenttype.handler|plugins/ktstandard/workflow/TypeAssociator.php|ktstandard.workflowassociation.documenttype.plugin', 'trigger', 'general'),
(253, 'ktstandard.triggers.workflowassociation.folder.handler', 'ktstandard.workflowassociation.folder.plugin', 'FolderWorkflowAssociator', 'plugins/ktstandard/workflow/FolderAssociator.php', 'workflow|objectModification|FolderWorkflowAssociator|ktstandard.triggers.workflowassociation.folder.handler|plugins/ktstandard/workflow/FolderAssociator.php|ktstandard.workflowassociation.folder.plugin', 'trigger', 'general'),
(254, 'misc/disclaimers', 'ktstandard.disclaimers.plugin', 'ManageDisclaimersDispatcher', 'plugins/ktstandard/admin/manageDisclaimers.php', 'disclaimers|ManageDisclaimersDispatcher|misc|Edit Disclaimers|Change disclaimers displayed on login and at the bottom of each page.|plugins/ktstandard/admin/manageDisclaimers.php||ktstandard.disclaimers.plugin', 'admin_page', 'general'),
(255, 'ktstandard.bulkexport.action', 'ktstandard.bulkexport.plugin', 'KTBulkExportAction', 'plugins/ktstandard/KTBulkExportPlugin.php', 'folderaction|KTBulkExportAction|ktstandard.bulkexport.action|plugins/ktstandard/KTBulkExportPlugin.php|ktstandard.bulkexport.plugin', 'action', 'general'),
(256, 'ktstandard.ktwebdavdashlet.dashlet', 'ktstandard.ktwebdavdashlet.plugin', 'KTWebDAVDashlet', 'plugins/ktstandard/KTWebDAVDashletPlugin.php', 'KTWebDAVDashlet|ktstandard.ktwebdavdashlet.dashlet|plugins/ktstandard/KTWebDAVDashletPlugin.php|ktstandard.ktwebdavdashlet.plugin', 'dashlet', 'dashboard'),
(257, 'ktcore.actions.document.immutable', 'ktstandard.immutableaction.plugin', 'KTDocumentImmutableAction', 'plugins/ktstandard/ImmutableActionPlugin.php', 'documentaction|KTDocumentImmutableAction|ktcore.actions.document.immutable|plugins/ktstandard/ImmutableActionPlugin.php|ktstandard.immutableaction.plugin', 'action', 'general'),
(258, 'ktcore.columns.preview', 'ktstandard.preview.plugin', 'PreviewColumn', 'plugins/ktstandard/documentpreview/documentPreviewPlugin.php', 'Property Preview|ktcore.columns.preview|PreviewColumn|plugins/ktstandard/documentpreview/documentPreviewPlugin.php', 'column', 'general'),
(259, 'ktstandard.preview.plugin', 'ktstandard.preview.plugin', 'ktstandard.preview.plugin', '/plugins/ktstandard/documentpreview/templates', 'documentpreview|/plugins/ktstandard/documentpreview/templates', 'locations', 'general'),
(260, 'ktstandard.pdf.generate', 'ktstandard.pdf.plugin', 'PDFGeneratorAction', 'plugins/ktstandard/PDFGeneratorPlugin.php', 'documentaction|PDFGeneratorAction|ktstandard.pdf.generate|plugins/ktstandard/PDFGeneratorPlugin.php|ktstandard.pdf.plugin', 'action', 'general'),
(261, 'ktstandard.authentication.ldapprovider', 'ktstandard.ldapauthentication.plugin', 'KTLDAPAuthenticationProvider', 'plugins/ktstandard/ldap/ldapauthenticationprovider.inc.php', 'LDAP Authentication|KTLDAPAuthenticationProvider|ktstandard.authentication.ldapprovider|plugins/ktstandard/ldap/ldapauthenticationprovider.inc.php|ktstandard.ldapauthentication.plugin', 'authentication_provider', 'general'),
(262, 'ktstandard.authentication.adprovider', 'ktstandard.ldapauthentication.plugin', 'KTActiveDirectoryAuthenticationProvider', 'plugins/ktstandard/ldap/activedirectoryauthenticationprovider.inc.php', 'ActiveDirectory Authentication|KTActiveDirectoryAuthenticationProvider|ktstandard.authentication.adprovider|plugins/ktstandard/ldap/activedirectoryauthenticationprovider.inc.php|ktstandard.ldapauthentication.plugin', 'authentication_provider', 'general'),
(263, 'ktcore.diskusage.dashlet', 'ktcore.housekeeper.plugin', 'DiskUsageDashlet', 'plugins/housekeeper/DiskUsageDashlet.inc.php', 'DiskUsageDashlet|ktcore.diskusage.dashlet|plugins/housekeeper/DiskUsageDashlet.inc.php|ktcore.housekeeper.plugin', 'dashlet', 'dashboard'),
(264, 'ktcore.folderusage.dashlet', 'ktcore.housekeeper.plugin', 'FolderUsageDashlet', 'plugins/housekeeper/FolderUsageDashlet.inc.php', 'FolderUsageDashlet|ktcore.folderusage.dashlet|plugins/housekeeper/FolderUsageDashlet.inc.php|ktcore.housekeeper.plugin', 'dashlet', 'dashboard'),
(265, 'ktcore.housekeeper.plugin', 'ktcore.housekeeper.plugin', 'ktcore.housekeeper.plugin', '/plugins/housekeeper/templates', 'housekeeper|/plugins/housekeeper/templates', 'locations', 'general'),
(266, 'password.reset.login.interceptor', 'password.reset.plugin', 'PasswordResetInterceptor', 'plugins/passwordResetPlugin/passwordResetPlugin.php', 'PasswordResetInterceptor|password.reset.login.interceptor|plugins/passwordResetPlugin/passwordResetPlugin.php', 'interceptor', 'general'),
(267, 'password.reset.plugin', 'password.reset.plugin', 'password.reset.plugin', '/var/www/installers/knowledgetree/plugins/passwordResetPlugin/templates', 'passwordResetPlugin|/var/www/installers/knowledgetree/plugins/passwordResetPlugin/templates', 'locations', 'general'),
(268, 'pdf.converter.processor', 'pdf.converter.processor.plugin', 'PDFConverter', 'plugins/pdfConverter/pdfConverter.php', 'PDFConverter|pdf.converter.processor|plugins/pdfConverter/pdfConverter.php', 'processor', ''),
(269, 'pdf.converter.triggers.delete', 'pdf.converter.processor.plugin', 'DeletePDFTrigger', 'plugins/pdfConverter/pdfConverterPlugin.php', 'delete|postValidate|DeletePDFTrigger|pdf.converter.triggers.delete|plugins/pdfConverter/pdfConverterPlugin.php|pdf.converter.processor.plugin', 'trigger', 'general'),
(270, 'ktcore.rss.plugin.folder.link', 'ktcore.rss.plugin', 'RSSFolderLinkAction', 'plugins/rssplugin/RSSPlugin.php', 'folderaction|RSSFolderLinkAction|ktcore.rss.plugin.folder.link|plugins/rssplugin/RSSPlugin.php|ktcore.rss.plugin', 'action', 'general'),
(271, 'ktcore.rss.plugin.document.link', 'ktcore.rss.plugin', 'RSSDocumentLinkAction', 'plugins/rssplugin/RSSPlugin.php', 'documentaction|RSSDocumentLinkAction|ktcore.rss.plugin.document.link|plugins/rssplugin/RSSPlugin.php|ktcore.rss.plugin', 'action', 'general'),
(272, 'ktcore.rss.feed.dashlet', 'ktcore.rss.plugin', 'RSSDashlet', 'plugins/rssplugin/RSSDashlet.php', 'RSSDashlet|ktcore.rss.feed.dashlet|plugins/rssplugin/RSSDashlet.php|ktcore.rss.plugin', 'dashlet', 'dashboard'),
(273, 'ktcore.dedicated.rss.feed.dashlet', 'ktcore.rss.plugin', 'RSSDedicatedDashlet', 'plugins/rssplugin/RSSDedicatedDashlet.php', 'RSSDedicatedDashlet|ktcore.dedicated.rss.feed.dashlet|plugins/rssplugin/RSSDedicatedDashlet.php|ktcore.rss.plugin', 'dashlet', 'dashboard'),
(274, 'ktcore.rss.plugin/managerssfeeds', 'ktcore.rss.plugin', 'ManageRSSFeedsDispatcher', 'plugins/rssplugin/RSSPlugin.php', 'ktcore.rss.plugin/managerssfeeds|ManageRSSFeedsDispatcher|plugins/rssplugin/RSSPlugin.php|ktcore.rss.plugin', 'page', 'general'),
(275, 'ktcore.rss.plugin', 'ktcore.rss.plugin', 'ktcore.rss.plugin', '/plugins/rssplugin/templates', 'RSS Plugin|/plugins/rssplugin/templates', 'locations', 'general'),
(276, 'nbm.browseable.dashlet', 'nbm.browseable.plugin', 'BrowseableFolderDashlet', 'plugins/browseabledashlet/BrowseableDashlet.php', 'BrowseableFolderDashlet|nbm.browseable.dashlet|plugins/browseabledashlet/BrowseableDashlet.php|nbm.browseable.plugin', 'dashlet', 'dashboard'),
(277, 'nbm.browseable.plugin', 'nbm.browseable.plugin', 'nbm.browseable.plugin', '/plugins/browseabledashlet/templates', 'browseabledashlet|/plugins/browseabledashlet/templates', 'locations', 'general'),
(278, 'thumbnails.generator.processor', 'thumbnails.generator.processor.plugin', 'thumbnailGenerator', 'plugins/thumbnails/thumbnails.php', 'thumbnailGenerator|thumbnails.generator.processor|plugins/thumbnails/thumbnails.php', 'processor', ''),
(279, 'thumbnail.viewlets', 'thumbnails.generator.processor.plugin', 'ThumbnailViewlet', 'plugins/thumbnails/thumbnails.php', 'documentviewlet|ThumbnailViewlet|thumbnail.viewlets|plugins/thumbnails/thumbnails.php|thumbnails.generator.processor.plugin', 'action', 'general'),
(280, 'thumbnails.generator.processor.plugin', 'thumbnails.generator.processor.plugin', 'thumbnails.generator.processor.plugin', '/var/www/installers/knowledgetree/plugins/thumbnails/templates', 'thumbnails|/var/www/installers/knowledgetree/plugins/thumbnails/templates', 'locations', 'general'),
(281, 'ktcore.criteria.tagcloud', 'ktcore.tagcloud.plugin', 'TagCloudCriterion', 'lib/browse/Criteria.inc', 'TagCloudCriterion|ktcore.criteria.tagcloud|lib/browse/Criteria.inc|', 'criterion', 'general'),
(282, 'ktcore.tagcloud.feed.dashlet', 'ktcore.tagcloud.plugin', 'TagCloudDashlet', 'plugins/tagcloud/TagCloudDashlet.php', 'TagCloudDashlet|ktcore.tagcloud.feed.dashlet|plugins/tagcloud/TagCloudDashlet.php|ktcore.tagcloud.plugin', 'dashlet', 'dashboard'),
(283, 'ktcore.tagcloud.plugin/TagCloudRedirection', 'ktcore.tagcloud.plugin', 'TagCloudRedirectPage', 'plugins/tagcloud/TagCloudPlugin.php', 'ktcore.tagcloud.plugin/TagCloudRedirection|TagCloudRedirectPage|plugins/tagcloud/TagCloudPlugin.php|ktcore.tagcloud.plugin', 'page', 'general'),
(284, 'tagcloud.portlet', 'ktcore.tagcloud.plugin', 'TagCloudPortlet', 'plugins/tagcloud/TagCloudPortlet.php', 'a:0:{}|TagCloudPortlet|tagcloud.portlet|plugins/tagcloud/TagCloudPortlet.php|ktcore.tagcloud.plugin', 'portlet', 'general'),
(285, 'ktcore.tagcloud.plugin', 'ktcore.tagcloud.plugin', 'ktcore.tagcloud.plugin', '/plugins/tagcloud/templates', 'Tag Cloud Plugin|/plugins/tagcloud/templates', 'locations', 'general'),
(286, 'config/officeaddinconfigpage', 'office.addin.plugin', 'OfficeAddInSettingsConfigPageDispatcher', 'plugins/commercial-plugins/officeaddin/dispatcher/officeaddindispatcher.php', 'officeaddinconfigpage|OfficeAddInSettingsConfigPageDispatcher|config|Office Add-In|View and change settings for the KnowledgeTree Office Add-In.|plugins/commercial-plugins/officeaddin/dispatcher/officeaddindispatcher.php||office.addin.plugin', 'admin_page', 'general'),
(287, 'ktcore.columns.custom.number', 'custom-numbering.plugin', 'CustomNumberingColumn', 'plugins/commercial-plugins/custom-numbering/CustomNumberingColumn.inc.php', 'Custom Document No|ktcore.columns.custom.number|CustomNumberingColumn|plugins/commercial-plugins/custom-numbering/CustomNumberingColumn.inc.php', 'column', 'general'),
(288, 'ktcore.custom.numbering.add', 'custom-numbering.plugin', 'CustomNumberingAddTrigger', 'plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php', 'content|scan|CustomNumberingAddTrigger|ktcore.custom.numbering.add|plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php|custom-numbering.plugin', 'trigger', 'general'),
(289, 'ktcore.custom.numbering.edit', 'custom-numbering.plugin', 'CustomNumberingEditTrigger', 'plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php', 'edit|postValidate|CustomNumberingEditTrigger|ktcore.custom.numbering.edit|plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php|custom-numbering.plugin', 'trigger', 'general'),
(290, 'ktcore.custom.numbering.copy', 'custom-numbering.plugin', 'CustomNumberingCopyTrigger', 'plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php', 'copyDocument|postValidate|CustomNumberingCopyTrigger|ktcore.custom.numbering.copy|plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php|custom-numbering.plugin', 'trigger', 'general'),
(291, 'ktcore.custom.numbering.rename', 'custom-numbering.plugin', 'CustomNumberingRenameTrigger', 'plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php', 'renameDocument|postValidate|CustomNumberingRenameTrigger|ktcore.custom.numbering.rename|plugins/commercial-plugins/custom-numbering/CustomNumberingTrigger.inc.php|custom-numbering.plugin', 'trigger', 'general'),
(292, 'custom-numbering.plugin', 'custom-numbering.plugin', 'custom-numbering.plugin', 'plugins/commercial-plugins/custom-numbering/templates', 'custom-numbering|plugins/commercial-plugins/custom-numbering/templates', 'locations', 'general'),
(293, 'documents/custom-numbering', 'custom-numbering.plugin', 'CustomNumberingAdminPage', 'plugins/commercial-plugins/custom-numbering/CustomNumberingAdminPage.inc.php', 'custom-numbering|CustomNumberingAdminPage|documents|Document Numbering Schemes|This allows for the customisation of a document numbering schemes|plugins/commercial-plugins/custom-numbering/CustomNumberingAdminPage.inc.php||custom-numbering.plugin', 'admin_page', 'general'),
(294, 'documents/typealertmanagement', 'document.alerts.plugin', 'KTDocTypeAlertDispatcher', 'plugins/commercial-plugins/alerts/docTypeAlerts.inc.php', 'typealertmanagement|KTDocTypeAlertDispatcher|documents|Alerts by Document Types|Manage alerts for the different document types within the system.|plugins/commercial-plugins/alerts/docTypeAlerts.inc.php||document.alerts.plugin', 'admin_page', 'general'),
(295, 'alerts.action.document.alert', 'document.alerts.plugin', 'AlertAction', 'plugins/commercial-plugins/alerts/alerts.php', 'documentaction|AlertAction|alerts.action.document.alert|plugins/commercial-plugins/alerts/alerts.php|document.alerts.plugin', 'action', 'general'),
(296, 'alerts.triggers.alert.delete', 'document.alerts.plugin', 'DeleteAlertTrigger', 'plugins/commercial-plugins/alerts/alerts.php', 'delete|postValidate|DeleteAlertTrigger|alerts.triggers.alert.delete|plugins/commercial-plugins/alerts/alerts.php|document.alerts.plugin', 'trigger', 'general'),
(297, 'alerts.triggers.alert.archive', 'document.alerts.plugin', 'ArchiveAlertTrigger', 'plugins/commercial-plugins/alerts/alerts.php', 'archive|postValidate|ArchiveAlertTrigger|alerts.triggers.alert.archive|plugins/commercial-plugins/alerts/alerts.php|document.alerts.plugin', 'trigger', 'general'),
(298, 'alerts.triggers.add.document.alert', 'document.alerts.plugin', 'AddDocumentAlertTrigger', 'plugins/commercial-plugins/alerts/docTypeAlerts.inc.php', 'add|postValidate|AddDocumentAlertTrigger|alerts.triggers.add.document.alert|plugins/commercial-plugins/alerts/docTypeAlerts.inc.php|document.alerts.plugin', 'trigger', 'general'),
(299, 'alerts.triggers.checkin.document.alert', 'document.alerts.plugin', 'CheckinDocumentAlertTrigger', 'plugins/commercial-plugins/alerts/docTypeAlerts.inc.php', 'checkin|postValidate|CheckinDocumentAlertTrigger|alerts.triggers.checkin.document.alert|plugins/commercial-plugins/alerts/docTypeAlerts.inc.php|document.alerts.plugin', 'trigger', 'general'),
(300, 'alerts.triggers.expuge.document', 'document.alerts.plugin', 'ExpungedDocumentTrigger', 'plugins/commercial-plugins/alerts/alerts.php', 'expunge|finalised|ExpungedDocumentTrigger|alerts.triggers.expuge.document|plugins/commercial-plugins/alerts/alerts.php|document.alerts.plugin', 'trigger', 'general'),
(301, 'alerts/alertnotification', 'document.alerts.plugin', 'AlertNotification', 'plugins/commercial-plugins/alerts/alerts.php', 'alerts/alertnotification|AlertNotification|plugins/commercial-plugins/alerts/alerts.php', 'notification_handler', 'general'),
(302, 'alerts/alertsubscription', 'document.alerts.plugin', 'AlertSubscriptionNotification', 'plugins/commercial-plugins/alerts/alerts.php', 'alerts/alertsubscription|AlertSubscriptionNotification|plugins/commercial-plugins/alerts/alerts.php', 'notification_handler', 'general'),
(303, 'alerts/archivedelete', 'document.alerts.plugin', 'ArchiveDeleteNotification', 'plugins/commercial-plugins/alerts/alerts.php', 'alerts/archivedelete|ArchiveDeleteNotification|plugins/commercial-plugins/alerts/alerts.php', 'notification_handler', 'general'),
(304, 'document.alerts.plugin', 'document.alerts.plugin', 'document.alerts.plugin', 'plugins/commercial-plugins/alerts/templates', 'alerts|plugins/commercial-plugins/alerts/templates', 'locations', 'general'),
(305, 'guid.inserter.processor', 'guid.inserter.plugin', 'GuidInserter', 'plugins/commercial-plugins/guidInserter/GuidInserter.php', 'GuidInserter|guid.inserter.processor|plugins/commercial-plugins/guidInserter/GuidInserter.php', 'processor', ''),
(306, 'guid.inserter.triggers.add.checkout', 'guid.inserter.plugin', 'GuidInserterCheckoutTrigger', 'plugins/commercial-plugins/guidInserter/GuidInserter.php', 'checkoutDownload|postValidate|GuidInserterCheckoutTrigger|guid.inserter.triggers.add.checkout|plugins/commercial-plugins/guidInserter/GuidInserter.php|guid.inserter.plugin', 'trigger', 'general'),
(307, 'guid.inserter.triggers.copy', 'guid.inserter.plugin', 'GuidInserterCopyTrigger', 'plugins/commercial-plugins/guidInserter/GuidInserter.php', 'copyDocument|postValidate|GuidInserterCopyTrigger|guid.inserter.triggers.copy|plugins/commercial-plugins/guidInserter/GuidInserter.php|guid.inserter.plugin', 'trigger', 'general'),
(308, 'guid.inserter.actions.restore', 'guid.inserter.plugin', 'GuidRestoreAction', 'plugins/commercial-plugins/guidInserter/GuidInserter.php', 'documentaction|GuidRestoreAction|guid.inserter.actions.restore|plugins/commercial-plugins/guidInserter/GuidInserter.php|guid.inserter.plugin', 'action', 'general'),
(309, 'shortcuts.actions.folder.addshortcut', 'shortcuts.plugin', 'FolderAddShortcutAction', 'plugins/commercial-plugins/shortcuts/FolderAddShortcutAction.php', 'folderaction|FolderAddShortcutAction|shortcuts.actions.folder.addshortcut|plugins/commercial-plugins/shortcuts/FolderAddShortcutAction.php|shortcuts.plugin', 'action', 'general'),
(310, 'electonic.signatures.validators.authenticate', 'electronic.signatures.plugin', 'KTSignatureValidator', 'plugins/commercial-plugins/electronic-signatures/KTElectronicSignaturesPlugin.php', 'KTSignatureValidator|electonic.signatures.validators.authenticate|plugins/commercial-plugins/electronic-signatures/KTElectronicSignaturesPlugin.php', 'validator', 'general'),
(311, 'electronic.signatures.plugin', 'electronic.signatures.plugin', 'electronic.signatures.plugin', 'plugins/commercial-plugins/electronic-signatures/templates', 'signatures|plugins/commercial-plugins/electronic-signatures/templates', 'locations', 'general'),
(312, 'document.comparison.plugin/DocumentComparison', 'document.comparison.plugin', 'DocumentComparison', 'plugins/commercial-plugins/documentcomparison/DocumentComparison.php', 'document.comparison.plugin/DocumentComparison|DocumentComparison|plugins/commercial-plugins/documentcomparison/DocumentComparison.php|document.comparison.plugin', 'page', 'general'),
(313, 'document.comparison.plugin', 'document.comparison.plugin', 'document.comparison.plugin', 'plugins/commercial-plugins/documentcomparison/templates', 'documentcomparison|plugins/commercial-plugins/documentcomparison/templates', 'locations', 'general'),
(314, 'bd.Quicklinks.dashlet', 'bd.Quicklinks.plugin', 'QuicklinksDashlet', 'plugins/commercial-plugins/network/quicklinks/QuicklinksDashlet.php', 'QuicklinksDashlet|bd.Quicklinks.dashlet|plugins/commercial-plugins/network/quicklinks/QuicklinksDashlet.php|bd.Quicklinks.plugin', 'dashlet', 'dashboard'),
(315, 'misc/adminquicklinksmanagement', 'bd.Quicklinks.plugin', 'adminManageQuicklinksDispatcher', 'plugins/commercial-plugins/network/quicklinks/manageQuicklinks.php', 'adminquicklinksmanagement|adminManageQuicklinksDispatcher|misc|Edit Quicklinks|Change the quicklinks that are displayed on user''s dashboards.|plugins/commercial-plugins/network/quicklinks/manageQuicklinks.php||bd.Quicklinks.plugin', 'admin_page', 'general'),
(316, 'bd.Quicklinks.plugin/quicklinksmanagement', 'bd.Quicklinks.plugin', 'ManageQuicklinksDispatcher', 'plugins/commercial-plugins/network/quicklinks/manageQuicklinks.php', 'bd.Quicklinks.plugin/quicklinksmanagement|ManageQuicklinksDispatcher|plugins/commercial-plugins/network/quicklinks/manageQuicklinks.php|bd.Quicklinks.plugin', 'page', 'general'),
(317, 'quicklinks.triggers.delete.document.quicklink', 'bd.Quicklinks.plugin', 'DeleteQuicklinkTrigger', 'plugins/commercial-plugins/network/quicklinks/manageQuicklinks.php', 'delete|postValidate|DeleteQuicklinkTrigger|quicklinks.triggers.delete.document.quicklink|plugins/commercial-plugins/network/quicklinks/manageQuicklinks.php|bd.Quicklinks.plugin', 'trigger', 'general'),
(318, 'bd.Quicklinks.plugin', 'bd.Quicklinks.plugin', 'bd.Quicklinks.plugin', '/plugins/commercial-plugins/network/quicklinks/templates/', 'QuicklinksDashlet|/plugins/commercial-plugins/network/quicklinks/templates/', 'locations', 'general'),
(319, 'ktnetwork.dashlet.gotodocumentid', 'ktnetwork.GoToDocumentId.plugin', 'GoToDocumentIdDashlet', 'plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php', 'GoToDocumentIdDashlet|ktnetwork.dashlet.gotodocumentid|plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php|ktnetwork.GoToDocumentId.plugin', 'dashlet', 'dashboard'),
(320, 'ktnetwork.GoToDocumentId.plugin/jump', 'ktnetwork.GoToDocumentId.plugin', 'GoToDocumentJumper', 'plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php', 'ktnetwork.GoToDocumentId.plugin/jump|GoToDocumentJumper|plugins/commercial-plugins/network/gotodocumentid/GoToDocumentIdPlugin.php|ktnetwork.GoToDocumentId.plugin', 'page', 'general'),
(321, 'ktnetwork.GoToDocumentId.plugin', 'ktnetwork.GoToDocumentId.plugin', 'ktnetwork.GoToDocumentId.plugin', '/plugins/commercial-plugins/network/gotodocumentid/templates/', 'gotodocid|/plugins/commercial-plugins/network/gotodocumentid/templates/', 'locations', 'general'),
(322, 'reporting', 'ktprofessional.reporting.plugin', 'Reporting', 'reporting', 'reporting|Reporting|Report on usage of KnowledgeTree', 'admin_category', 'general'),
(323, 'reporting/users', 'ktprofessional.reporting.plugin', 'KTUserReportingDispatcher', 'plugins/commercial-plugins/professional-reporting/admin/userReporting.php', 'users|KTUserReportingDispatcher|reporting|User reports|Reports on user activities - login history and last login information.|plugins/commercial-plugins/professional-reporting/admin/userReporting.php||ktprofessional.reporting.plugin', 'admin_page', 'general'),
(324, 'ktprofessional.reporting.plugin', 'ktprofessional.reporting.plugin', 'ktprofessional.reporting.plugin', 'plugins/commercial-plugins/professional-reporting/templates', 'ktprofessional.reporting.plugin|plugins/commercial-plugins/professional-reporting/templates', 'locations', 'general'),
(325, 'instaview.generator.processor', 'instaview.processor.plugin', 'InstaView', 'plugins/commercial-plugins/instaView/instaView.php', 'InstaView|instaview.generator.processor|plugins/commercial-plugins/instaView/instaView.php', 'processor', ''),
(326, 'ktnetwork.inlineview.plugin', 'ktnetwork.inlineview.plugin', 'ktnetwork.inlineview.plugin', '/plugins/commercial-plugins/network/inlineview/templates/', 'inlineview|/plugins/commercial-plugins/network/inlineview/templates/', 'locations', 'general'),
(327, 'ktnetwork.inlineview.actions.view', 'ktnetwork.inlineview.plugin', 'InlineViewAction', 'plugins/commercial-plugins/network/inlineview/InlineViewPlugin.php', 'documentaction|InlineViewAction|ktnetwork.inlineview.actions.view|plugins/commercial-plugins/network/inlineview/InlineViewPlugin.php|ktnetwork.inlineview.plugin', 'action', 'general'),
(328, 'ktnetwork.dashlet.topdownloads', 'ktnetwork.TopDownloads.plugin', 'TopDownloadsDashlet', 'plugins/commercial-plugins/network/topdownloads/TopDownloadsPlugin.php', 'TopDownloadsDashlet|ktnetwork.dashlet.topdownloads|plugins/commercial-plugins/network/topdownloads/TopDownloadsPlugin.php|ktnetwork.TopDownloads.plugin', 'dashlet', 'dashboard'),
(329, 'ktnetwork.TopDownloads.plugin', 'ktnetwork.TopDownloads.plugin', 'ktnetwork.TopDownloads.plugin', '/plugins/commercial-plugins/network/topdownloads/templates/', 'topdownloads|/plugins/commercial-plugins/network/topdownloads/templates/', 'locations', 'general'),
(330, 'brad.UserHistory.folderaction', 'brad.UserHistory.plugin', 'UserHistoryFolderAction', 'plugins/commercial-plugins/network/userhistory/UserHistoryActions.php', 'folderaction|UserHistoryFolderAction|brad.UserHistory.folderaction|plugins/commercial-plugins/network/userhistory/UserHistoryActions.php|brad.UserHistory.plugin', 'action', 'general'),
(331, 'brad.UserHistory.documentaction', 'brad.UserHistory.plugin', 'UserHistoryDocumentAction', 'plugins/commercial-plugins/network/userhistory/UserHistoryActions.php', 'documentaction|UserHistoryDocumentAction|brad.UserHistory.documentaction|plugins/commercial-plugins/network/userhistory/UserHistoryActions.php|brad.UserHistory.plugin', 'action', 'general'),
(332, 'brad.UserHistory.dashlet', 'brad.UserHistory.plugin', 'UserHistoryDashlet', 'plugins/commercial-plugins/network/userhistory/UserHistoryDashlet.inc.php', 'UserHistoryDashlet|brad.UserHistory.dashlet|plugins/commercial-plugins/network/userhistory/UserHistoryDashlet.inc.php|brad.UserHistory.plugin', 'dashlet', 'dashboard'),
(333, 'brad.UserHistory.plugin', 'brad.UserHistory.plugin', 'brad.UserHistory.plugin', '/plugins/commercial-plugins/network/userhistory/templates/', 'userhistory|/plugins/commercial-plugins/network/userhistory/templates/', 'locations', 'general'),
(334, 'misc/extendedtransactionreports', 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'KTExtendedTransactionReports', 'plugins/commercial-plugins/network/extendedtransactioninfo/adminReports.php', 'extendedtransactionreports|KTExtendedTransactionReports|misc|Extended Transaction Information|View detailed information about user activity.|plugins/commercial-plugins/network/extendedtransactioninfo/adminReports.php||ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'admin_page', 'general'),
(335, 'ktnetwork.actions.folder.useractivityreports', 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'KTUserActivityReports', 'plugins/commercial-plugins/network/extendedtransactioninfo/standardReports.php', 'folderaction|KTUserActivityReports|ktnetwork.actions.folder.useractivityreports|plugins/commercial-plugins/network/extendedtransactioninfo/standardReports.php|ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'action', 'general'),
(336, 'ktnetwork.dashlet.recentchanges', 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'KTRecentDocsDashlet', 'plugins/commercial-plugins/network/extendedtransactioninfo/latestchanges.php', 'KTRecentDocsDashlet|ktnetwork.dashlet.recentchanges|plugins/commercial-plugins/network/extendedtransactioninfo/latestchanges.php|ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'dashlet', 'dashboard'),
(337, 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', 'ktnetwork.ExtendedDocumentTransactionInfo.plugin', '/plugins/commercial-plugins/network/extendedtransactioninfo/templates/', 'extendedtransactioninfo|/plugins/commercial-plugins/network/extendedtransactioninfo/templates/', 'locations', 'general'),
(338, 'klive.mydropdocuments.dashlet', 'ktlive.mydropdocuments.plugin', 'MyDropDocumentsDashlet', 'plugins/MyDropDocumentsPlugin/MyDropDocumentsDashlet.php', 'MyDropDocumentsDashlet|klive.mydropdocuments.dashlet|plugins/MyDropDocumentsPlugin/MyDropDocumentsDashlet.php|ktlive.mydropdocuments.plugin', 'dashlet', 'dashboard'),
(339, 'ktlive.mydropdocuments.plugin/MyDropDocuments', 'ktlive.mydropdocuments.plugin', 'MyDropDocumentsPage', 'plugins/MyDropDocumentsPlugin/MyDropDocumentsPage.php', 'ktlive.mydropdocuments.plugin/MyDropDocuments|MyDropDocumentsPage|plugins/MyDropDocumentsPlugin/MyDropDocumentsPage.php|ktlive.mydropdocuments.plugin', 'page', 'general'),
(340, 'ktlive.mydropdocuments.plugin', 'ktlive.mydropdocuments.plugin', 'ktlive.mydropdocuments.plugin', '/plugins/MyDropDocumentsPlugin/templates', 'MyDropDocumentsDashlet|/plugins/MyDropDocumentsPlugin/templates', 'locations', 'general');

--
-- Dumping data for table `plugin_rss`
--


--
-- Dumping data for table `quicklinks`
--


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

--
-- Dumping data for table `role_allocations`
--

INSERT INTO `role_allocations` (`id`, `folder_id`, `role_id`, `permission_descriptor_id`) VALUES
(1, 2, 5, 4),
(2, 3, 5, 4);

--
-- Dumping data for table `saved_searches`
--


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
(12, 'Document Alerts', 'plugins/commercial-plugins/alerts/alertTask.php', '', 0, 'daily', '2009-09-09 06:00:00', '2009-09-08 06:00:00', 0, 'enabled');

--
-- Dumping data for table `search_document_user_link`
--


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

--
-- Dumping data for table `search_saved`
--


--
-- Dumping data for table `search_saved_events`
--


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

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `value`) VALUES
(1, 'lastIndexUpdate', '0'),
(2, 'knowledgeTreeVersion', '3.6.3\n'),
(3, 'databaseVersion', '3.6.3'),
(4, 'server_name', '127.0.0.1'),
(6, 'dashboard-state-1', '{"left":[{"id":"KTInfoDashlet","state":0},{"id":"TagCloudDashlet","state":0},{"id":"RSSDedicatedDashlet","state":0},{"id":"KTMailServerDashlet","state":0},{"id":"BrowseableFolderDashlet","state":0},{"id":"MyDropDocumentsDashlet","state":0},{"id":"LicenseDashlet","state":0},{"id":"GoToDocumentIdDashlet","state":0},{"id":"KTRecentDocsDashlet","state":0}],"right":[{"id":"RSSDashlet","state":0},{"id":"schedulerDashlet","state":0},{"id":"KTWebDAVDashlet","state":0},{"id":"WinToolsNagDashlet","state":0},{"id":"QuicklinksDashlet","state":0},{"id":"TopDownloadsDashlet","state":0}]}');

--
-- Dumping data for table `tag_words`
--


--
-- Dumping data for table `time_period`
--


--
-- Dumping data for table `time_unit_lookup`
--

INSERT INTO `time_unit_lookup` (`id`, `name`) VALUES
(1, 'Years'),
(2, 'Months'),
(3, 'Days');

--
-- Dumping data for table `trigger_selection`
--


--
-- Dumping data for table `type_workflow_map`
--


--
-- Dumping data for table `units_lookup`
--


--
-- Dumping data for table `units_organisations_link`
--


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
(222, 'func*3.6.1*0*removeSlashesFromObjects', 'Remove slashes from documents and folders', '2009-09-08 16:04:35', 1, 'upgrade*3.6.3*99*upgrade3.6.3'),
(223, 'sql*3.6.2*0*3.6.2/data_types.sql', 'Database upgrade to version 3.6.2: Data types', '2009-09-08 16:04:35', 1, 'upgrade*3.6.3*99*upgrade3.6.3'),
(224, 'sql*3.6.2*0*3.6.2/folders.sql', 'Database upgrade to version 3.6.2: Folders', '2009-09-08 16:04:35', 1, 'upgrade*3.6.3*99*upgrade3.6.3'),
(225, 'upgrade*3.6.3*99*upgrade3.6.3', 'Upgrade from version 3.6.1 to 3.6.3', '2009-09-08 16:05:41', 1, 'upgrade*3.6.3*99*upgrade3.6.3');

--
-- Dumping data for table `uploaded_files`
--


--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `password`, `quota_max`, `quota_current`, `email`, `mobile`, `email_notification`, `sms_notification`, `authentication_details_s1`, `max_sessions`, `language_id`, `authentication_details_s2`, `authentication_source_id`, `authentication_details_b1`, `authentication_details_i2`, `authentication_details_d1`, `authentication_details_i1`, `authentication_details_d2`, `authentication_details_b2`, `last_login`, `disabled`) VALUES
(-2, 'anonymous', 'Anonymous', '---------------', 0, 0, NULL, NULL, 0, 0, NULL, 30000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(1, 'admin', 'Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', 1, 1, '', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2009-09-08 16:05:01', 0);

--
-- Dumping data for table `users_groups_link`
--

INSERT INTO `users_groups_link` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1);

--
-- Dumping data for table `user_history`
--

INSERT INTO `user_history` (`id`, `datetime`, `user_id`, `action_namespace`, `comments`, `session_id`) VALUES
(1, '2009-09-08 16:04:30', 1, 'ktcore.user_history.login', 'Logged in from 127.0.0.1', 1),
(2, '2009-09-08 16:05:01', 1, 'ktcore.user_history.login', 'Logged in from 127.0.0.1', 2);

--
-- Dumping data for table `user_history_documents`
--


--
-- Dumping data for table `user_history_folders`
--


--
-- Dumping data for table `workflows`
--

INSERT INTO `workflows` (`id`, `name`, `human_name`, `start_state_id`, `enabled`) VALUES
(2, 'Review Process', 'Review Process', 2, 1),
(3, 'Generate Document', 'Generate Document', 5, 1);

--
-- Dumping data for table `workflow_actions`
--


--
-- Dumping data for table `workflow_documents`
--


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

--
-- Dumping data for table `workflow_state_actions`
--


--
-- Dumping data for table `workflow_state_disabled_actions`
--


--
-- Dumping data for table `workflow_state_permission_assignments`
--


--
-- Dumping data for table `workflow_state_transitions`
--

INSERT INTO `workflow_state_transitions` (`state_id`, `transition_id`) VALUES
(2, 2),
(3, 3),
(3, 4),
(5, 5),
(6, 6);

--
-- Dumping data for table `workflow_transitions`
--

INSERT INTO `workflow_transitions` (`id`, `workflow_id`, `name`, `human_name`, `target_state_id`, `guard_permission_id`, `guard_group_id`, `guard_role_id`, `guard_condition_id`) VALUES
(2, 2, 'Request Approval', 'Request Approval', 3, NULL, NULL, NULL, NULL),
(3, 2, 'Reject', 'Reject', 2, NULL, NULL, NULL, NULL),
(4, 2, 'Approve', 'Approve', 4, NULL, NULL, NULL, NULL),
(5, 3, 'Draft Completed', 'Draft Completed', 6, NULL, NULL, NULL, NULL),
(6, 3, 'Publish', 'Publish', 7, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `workflow_trigger_instances`
--


--
-- Dumping data for table `zseq_active_sessions`
--

INSERT INTO `zseq_active_sessions` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_archive_restoration_request`
--

INSERT INTO `zseq_archive_restoration_request` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_archiving_settings`
--

INSERT INTO `zseq_archiving_settings` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_archiving_type_lookup`
--

INSERT INTO `zseq_archiving_type_lookup` (`id`) VALUES
(2);

--
-- Dumping data for table `zseq_authentication_sources`
--

INSERT INTO `zseq_authentication_sources` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_baobab_keys`
--


--
-- Dumping data for table `zseq_baobab_user_keys`
--


--
-- Dumping data for table `zseq_column_entries`
--

INSERT INTO `zseq_column_entries` (`id`) VALUES
(15);

--
-- Dumping data for table `zseq_config_settings`
--


--
-- Dumping data for table `zseq_dashlet_disables`
--

INSERT INTO `zseq_dashlet_disables` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_data_types`
--

INSERT INTO `zseq_data_types` (`id`) VALUES
(5);

--
-- Dumping data for table `zseq_discussion_comments`
--

INSERT INTO `zseq_discussion_comments` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_discussion_threads`
--

INSERT INTO `zseq_discussion_threads` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_documents`
--

INSERT INTO `zseq_documents` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_archiving_link`
--

INSERT INTO `zseq_document_archiving_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_content_version`
--

INSERT INTO `zseq_document_content_version` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_fields`
--

INSERT INTO `zseq_document_fields` (`id`) VALUES
(5);

--
-- Dumping data for table `zseq_document_fields_link`
--

INSERT INTO `zseq_document_fields_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_link`
--

INSERT INTO `zseq_document_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_link_types`
--

INSERT INTO `zseq_document_link_types` (`id`) VALUES
(5);

--
-- Dumping data for table `zseq_document_metadata_version`
--

INSERT INTO `zseq_document_metadata_version` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_role_allocations`
--


--
-- Dumping data for table `zseq_document_subscriptions`
--

INSERT INTO `zseq_document_subscriptions` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_tags`
--

INSERT INTO `zseq_document_tags` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_transactions`
--

INSERT INTO `zseq_document_transactions` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_transaction_types_lookup`
--

INSERT INTO `zseq_document_transaction_types_lookup` (`id`) VALUES
(21);

--
-- Dumping data for table `zseq_document_types_lookup`
--

INSERT INTO `zseq_document_types_lookup` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_type_fieldsets_link`
--

INSERT INTO `zseq_document_type_fieldsets_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_document_type_fields_link`
--

INSERT INTO `zseq_document_type_fields_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_fieldsets`
--

INSERT INTO `zseq_fieldsets` (`id`) VALUES
(3);

--
-- Dumping data for table `zseq_field_behaviours`
--

INSERT INTO `zseq_field_behaviours` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_field_value_instances`
--

INSERT INTO `zseq_field_value_instances` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_folders`
--

INSERT INTO `zseq_folders` (`id`) VALUES
(2);

--
-- Dumping data for table `zseq_folders_users_roles_link`
--

INSERT INTO `zseq_folders_users_roles_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_folder_doctypes_link`
--

INSERT INTO `zseq_folder_doctypes_link` (`id`) VALUES
(2);

--
-- Dumping data for table `zseq_folder_subscriptions`
--

INSERT INTO `zseq_folder_subscriptions` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_folder_transactions`
--


--
-- Dumping data for table `zseq_groups_groups_link`
--

INSERT INTO `zseq_groups_groups_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_groups_lookup`
--

INSERT INTO `zseq_groups_lookup` (`id`) VALUES
(3);

--
-- Dumping data for table `zseq_help`
--

INSERT INTO `zseq_help` (`id`) VALUES
(100);

--
-- Dumping data for table `zseq_help_replacement`
--

INSERT INTO `zseq_help_replacement` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_interceptor_instances`
--


--
-- Dumping data for table `zseq_links`
--

INSERT INTO `zseq_links` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_metadata_lookup`
--

INSERT INTO `zseq_metadata_lookup` (`id`) VALUES
(11);

--
-- Dumping data for table `zseq_metadata_lookup_tree`
--

INSERT INTO `zseq_metadata_lookup_tree` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_mime_documents`
--


--
-- Dumping data for table `zseq_mime_extractors`
--

INSERT INTO `zseq_mime_extractors` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_mime_types`
--

INSERT INTO `zseq_mime_types` (`id`) VALUES
(171);

--
-- Dumping data for table `zseq_news`
--

INSERT INTO `zseq_news` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_notifications`
--

INSERT INTO `zseq_notifications` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_organisations_lookup`
--

INSERT INTO `zseq_organisations_lookup` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_permissions`
--

INSERT INTO `zseq_permissions` (`id`) VALUES
(8);

--
-- Dumping data for table `zseq_permission_assignments`
--

INSERT INTO `zseq_permission_assignments` (`id`) VALUES
(8);

--
-- Dumping data for table `zseq_permission_descriptors`
--

INSERT INTO `zseq_permission_descriptors` (`id`) VALUES
(2);

--
-- Dumping data for table `zseq_permission_dynamic_conditions`
--

INSERT INTO `zseq_permission_dynamic_conditions` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_permission_lookups`
--

INSERT INTO `zseq_permission_lookups` (`id`) VALUES
(5);

--
-- Dumping data for table `zseq_permission_lookup_assignments`
--

INSERT INTO `zseq_permission_lookup_assignments` (`id`) VALUES
(24);

--
-- Dumping data for table `zseq_permission_objects`
--

INSERT INTO `zseq_permission_objects` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_plugins`
--

INSERT INTO `zseq_plugins` (`id`) VALUES
(22);

--
-- Dumping data for table `zseq_plugin_helper`
--


--
-- Dumping data for table `zseq_plugin_rss`
--

INSERT INTO `zseq_plugin_rss` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_quicklinks`
--


--
-- Dumping data for table `zseq_roles`
--

INSERT INTO `zseq_roles` (`id`) VALUES
(4);

--
-- Dumping data for table `zseq_role_allocations`
--

INSERT INTO `zseq_role_allocations` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_saved_searches`
--

INSERT INTO `zseq_saved_searches` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_scheduler_tasks`
--

INSERT INTO `zseq_scheduler_tasks` (`id`) VALUES
(10);

--
-- Dumping data for table `zseq_search_saved`
--


--
-- Dumping data for table `zseq_status_lookup`
--

INSERT INTO `zseq_status_lookup` (`id`) VALUES
(6);

--
-- Dumping data for table `zseq_system_settings`
--

INSERT INTO `zseq_system_settings` (`id`) VALUES
(3);

--
-- Dumping data for table `zseq_tag_words`
--

INSERT INTO `zseq_tag_words` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_time_period`
--

INSERT INTO `zseq_time_period` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_time_unit_lookup`
--

INSERT INTO `zseq_time_unit_lookup` (`id`) VALUES
(3);

--
-- Dumping data for table `zseq_units_lookup`
--

INSERT INTO `zseq_units_lookup` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_units_organisations_link`
--

INSERT INTO `zseq_units_organisations_link` (`id`) VALUES
(1);

--
-- Dumping data for table `zseq_upgrades`
--

INSERT INTO `zseq_upgrades` (`id`) VALUES
(221);

--
-- Dumping data for table `zseq_users`
--

INSERT INTO `zseq_users` (`id`) VALUES
(3);

--
-- Dumping data for table `zseq_users_groups_link`
--

INSERT INTO `zseq_users_groups_link` (`id`) VALUES
(3);

--
-- Dumping data for table `zseq_user_history`
--


--
-- Dumping data for table `zseq_user_history_documents`
--


--
-- Dumping data for table `zseq_user_history_folders`
--


--
-- Dumping data for table `zseq_workflows`
--

INSERT INTO `zseq_workflows` (`id`) VALUES
(3);

--
-- Dumping data for table `zseq_workflow_states`
--

INSERT INTO `zseq_workflow_states` (`id`) VALUES
(7);

--
-- Dumping data for table `zseq_workflow_state_disabled_actions`
--


--
-- Dumping data for table `zseq_workflow_state_permission_assignments`
--


--
-- Dumping data for table `zseq_workflow_transitions`
--

INSERT INTO `zseq_workflow_transitions` (`id`) VALUES
(6);

--
-- Dumping data for table `zseq_workflow_trigger_instances`
--

