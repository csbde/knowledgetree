-- default dms user
GRANT SELECT, INSERT, UPDATE, DELETE ON * TO dms@localhost IDENTIFIED BY 'djw9281js';
-- admin dms user
GRANT ALL PRIVILEGES ON * TO dmsadmin@localhost IDENTIFIED BY 'js9281djw';
-- restricted indexer user
GRANT SELECT ON documents TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON folders TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON groups_folders_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON users_groups_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON folders_users_roles_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON users TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON groups_lookup TO indexer@localhost IDENTIFIED BY 'idx158pqg';

GRANT SELECT, UPDATE ON system_settings TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON document_transactions TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON documents TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT SELECT ON folders TO indexer@localhost IDENTIFIED BY 'idx158pqg';

GRANT SELECT, INSERT, DELETE ON document_text TO indexer@localhost IDENTIFIED BY 'idx158pqg';
GRANT INSERT, DELETE ON search_document_user_link TO indexer@localhost IDENTIFIED BY 'idx158pqg';
-- restricted archiver user
GRANT SELECT, UPDATE ON documents TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON time_period TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON time_unit_lookup TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON document_transactions TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON document_archiving_link TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON archiving_settings TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON archiving_type_lookup TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
GRANT SELECT ON folders_users_roles_link TO archiver@localhost IDENTIFIED BY 'ar(h923v3R';
