INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit) VALUES

('browse', 'Truncate Document and Folder Titles in Browse View', 'Defines the length of the document or folder title displayed in the
browse view.', 'titleCharLength', 'default', '40', 'numeric_string', '', 1),

('import', 'Disable Bulk Import', 'Disable the bulk import plugin', 'disableBulkImport', 'default', 'false', 'string', NULL, 1),

('session', 'Enable version check', 'Compares the system version with the database version to determine if a database upgrade is needed.',
'dbversioncompare', 'default', 'true', 'boolean', NULL, 0);