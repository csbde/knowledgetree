-- default dms user
GRANT SELECT, INSERT, UPDATE, DELETE ON dms.* TO dms@localhost IDENTIFIED BY 'djw9281js';
-- restricted indexer user
GRANT SELECT ON dms.documents TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.folders TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.groups_folders_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.users_groups_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.folders_users_roles_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.users TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.groups_lookup TO indexer@localhost IDENTIFIED BY 'idx158pqg';

GRANT SELECT, UPDATE ON dms.system_settings TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.document_transactions TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.documents TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON dms.folders TO indexer@localhost IDENTIFIED BY 'idx158pqg';

GRANT INSERT, DELETE ON dms.document_text TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT INSERT, DELETE ON dms.search_document_user_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
-- restricted archiver user
GRANT SELECT, UPDATE ON dms.documents TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON time_period TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON time_unit_lookup TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON document_transactions TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON document_archiving_link TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON archiving_settings TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON archiving_type_lookup TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON folders_users_roles_link TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';