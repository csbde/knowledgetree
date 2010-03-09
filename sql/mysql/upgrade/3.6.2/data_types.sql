INSERT INTO data_types VALUES(6, 'LARGE TEXT');
INSERT INTO data_types VALUES(7, 'DATE');

ALTER TABLE document_fields ADD COLUMN is_html tinyint(1) default null;
ALTER TABLE document_fields ADD COLUMN max_length int default null;