-- default dms user
GRANT SELECT, INSERT, UPDATE, DELETE ON dms.* TO dms@localhost IDENTIFIED BY 'djw9281js';
-- restricted indexer user
GRANT SELECT ON dms.documents TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.folders TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT, INSERT, UPDATE, DELETE ON dms.document_words_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT, INSERT, UPDATE, DELETE ON dms.words_lookup TO indexer@localhost IDENTIFIED BY 'idx158pqg';
