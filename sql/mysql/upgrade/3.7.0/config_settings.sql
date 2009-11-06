INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('urls', 'Internal Var Directory', 'The path to the internal var directory that must sit within the web root', 'internalVarDirectory', 'default', '${fileSystemRoot}/var', 'string', NULL, 0);

INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('urls', 'PDF Directoy', 'The path for storing the generated PDF Documents', 'pdfDirectory', 'default', '${varDirectory}/Pdf', 'string', '', 1);