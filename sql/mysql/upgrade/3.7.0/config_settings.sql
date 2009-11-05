INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('urls', 'Internal Var Directory', 'The path to the internal var directory that must sit within the web root', 'internalVarDirectory', 'default', '${fileSystemRoot}/var', 'string', NULL, 0);

INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('externalBinary', 'zip', 'The path to the zip binary', 'zipPath', 'default', 'zip', 'string', NULL, 0);

INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('externalBinary', 'unzip', 'The path to the unzip binary', 'unzipPath', 'default', 'unzip', 'string', NULL, 0);