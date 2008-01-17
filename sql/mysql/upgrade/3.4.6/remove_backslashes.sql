UPDATE help_replacement
SET description = replace(replace(replace(description, '\\\\', '\\'), '\\\"', '\"'), '\\\'', '\''),
 title = replace(replace(replace(title, '\\\\', '\\'), '\\\"', '\"'), '\\\'', '\'');

UPDATE document_metadata_version
SET name = replace(replace(replace(name, '\\\\', '\\'), '\\\"', '\"'), '\\\'', '\''),
 description = replace(replace(replace(description, '\\\\', '\\'), '\\\"', '\"'), '\\\'', '\'');

UPDATE folders
SET name = replace(replace(replace(name, '\\\\', '\\'), '\\\"', '\"'), '\\\'', '\''),
 description = replace(replace(replace(description, '\\\\', '\\'), '\\\"', '\"'), '\\\'', '\'');