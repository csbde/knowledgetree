UPDATE zseq_system_settings SET id=LAST_INSERT_ID(id+1);
INSERT INTO system_settings VALUES (LAST_INSERT_ID(), 'databaseVersion', '2.0.6');
