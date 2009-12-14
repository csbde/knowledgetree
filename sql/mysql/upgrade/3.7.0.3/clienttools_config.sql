# Configuration Settings for Explorer CP v0.9a server side logging.
INSERT INTO config_groups(`name`, `display_name`, `description`, `category`)
VALUES('explorerCPSettings', 'Explorer CP Settings', 'Configuration options for KnowledgeTree Explorer CP', 'Client Tools Settings');

INSERT INTO config_settings(`group_name`, `display_name`, `description`, `item`, `value`, `default_value`, `type`, `options`, `can_edit`)
VALUES('explorerCPSettings', 'Debug Log Level', 'Set the level of debug information included in the server side log file', 'debugLevel', 'error', 'error', 'dropdown', 'a:1:{s:7:\"options\";a:3:{i:0;a:2:{s:5:\"value\";s:3:\"off\";s:5:\"label\";s:10:\"No Logging\";}i:1;a:2:{s:5:\"value\";s:5:\"error\";s:5:\"label\";s:18:\"Error Logging Only\";}i:2;a:2:{s:5:\"value\";s:5:\"debug\";s:5:\"label\";s:28:\"Error and Debug Info Logging\";}}}', 1);
