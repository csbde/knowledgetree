INSERT INTO config_groups (name, display_name, description, category) VALUES
('server', 'Server Settings', 'Configuration settings for the server', 'General Settings');


INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit) VALUES
('server', 'Internal Server IP', 'The internal IP for the server, this is usually set to 127.0.0.1.', 'internal_server_name', 'default', '127.0.0.1', 'string', NULL, 1),
('server', 'Internal Server port', 'The internal port for the server.', 'internal_server_port', 'default', '80', 'numeric_string', NULL, 1),
('server', 'External Server IP', 'The external IP for the server.', 'server_name', 'default', '', 'string', NULL, 1),
('server', 'External Server port', 'The external port for the server.', 'server_port', 'default', '', 'numeric_string', NULL, 1),
('KnowledgeTree', 'Root Url', 'The path to the web application from the root of the web server. For example, if KT is at http://example.org/foo/, then the root directory should be \'/foo\'.', 'rootUrl', '', '', 'string', NULL, 1),
('urls', 'Var Directory', 'The path to the var directory.', 'varDirectory', 'default', '${fileSystemRoot}/var', 'string', NULL, 1);
