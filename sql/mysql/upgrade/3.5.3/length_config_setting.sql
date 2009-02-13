INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit) VALUES

('browse', 'Truncate Document and Folder Titles in Browse View', 'Defines the maximum number of characters to display for a document or folder title in the browse view. The maximum allowable number of characters is 255.', 'titleCharLength', 'default', '40', 'numeric_string', NULL, 1),

('import', 'Disable Bulk Import', 'Disable the bulk import plugin', 'disableBulkImport', 'default', 'false', 'string', NULL, 1),

('session', 'Enable version check', 'Compares the system version with the database version to determine if a database upgrade is needed.', 'dbversioncompare', 'default', 'true', 'boolean', NULL, 0),

('tweaks', 'Update Document Version (Content) on Editing Metadata', 'The document version is equivalent to the document content version. When set to true the document version will be increased when the document metadata is updated.', 'updateContentVersion', 'default', 'false', 'boolean', NULL, 1),

('tweaks', 'Always Force Original Filename on Checkin', 'When set to true, the checkbox for "Force Original Filename" will be hidden on check-in. This ensures that the filename will always stay the same.', 'disableForceFilenameOption', 'default', 'false', 'boolean', NULL, 1),

('KnowledgeTree', 'The Location of the Mime Magic File', 'The path to the mime magic database file.', 'magicDatabase', 'default', '/usr/share/file/magic', 'string', NULL, 1);

UPDATE config_settings SET default_value = 'Add Company Name' WHERE group_name = 'ui' AND item = 'companyLogoTitle';