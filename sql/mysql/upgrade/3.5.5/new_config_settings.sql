INSERT INTO `config_settings`
(group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES
('indexer', 'Enable the Document Indexer', 'Enables the indexing of document content for full text searching.', 'enableIndexing', 'default', 'true', 'boolean', NULL, 1);
INSERT INTO `config_settings`
(group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES
('tweaks', 'Increment version on rename', 'Defines whether to update the version number if a document filename is changed/renamed.', 'incrementVersionOnRename', 'default', 'true', 'boolean', NULL, 1);