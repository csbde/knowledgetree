-- default dms user
GRANT SELECT, INSERT, UPDATE, DELETE ON * TO dms@localhost IDENTIFIED BY 'djw9281js';
-- admin dms user
GRANT ALL PRIVILEGES ON * TO dmsadmin@localhost IDENTIFIED BY 'js9281djw';
