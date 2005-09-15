ALTER TABLE `documents` ADD `storage_path` VARCHAR( 250 ) ;
ALTER TABLE `documents` ADD INDEX ( `storage_path` ) ;
UPDATE `documents` SET storage_path = CONCAT(full_path, "/", filename) WHERE storage_path IS NULL;
