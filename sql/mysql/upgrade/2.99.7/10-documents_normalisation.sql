

ALTER TABLE `documents` DROP COLUMN `document_type_id`; # was int(11) NOT NULL default '0'
ALTER TABLE `documents` DROP COLUMN `size`; # was bigint(20) NOT NULL default '0'
ALTER TABLE `documents` DROP COLUMN `description`; # was varchar(200) NOT NULL default ''
ALTER TABLE `documents` DROP COLUMN `security`; # was int(11) NOT NULL default '0'
ALTER TABLE `documents` DROP COLUMN `major_version`; # was int(11) NOT NULL default '0'
ALTER TABLE `documents` DROP COLUMN `minor_version`; # was int(11) NOT NULL default '0'
ALTER TABLE `documents` DROP COLUMN `live_document_id`; # was int(11) default NULL
ALTER TABLE `documents` DROP COLUMN `filename`; # was text NOT NULL
ALTER TABLE `documents` DROP COLUMN `storage_path`; # was varchar(250) default NULL
ALTER TABLE `documents` DROP COLUMN `mime_id`; # was int(11) NOT NULL default '0'
ALTER TABLE `documents` DROP COLUMN `name`; # was text NOT NULL
