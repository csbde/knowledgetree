ALTER TABLE `documents` ADD `storage_path` VARCHAR( 250 ) ;
ALTER TABLE `documents` ADD INDEX ( `storage_path` ) ;
UPDATE `documents` d SET storage_path = (select CONCAT(CONCAT(CONCAT(CONCAT(f.full_path, "/"), f.name), "/"), filename) from folders f where f.id=d.folder_id) WHERE storage_path IS NULL;
UPDATE `documents` d SET storage_path=CONCAT(CONCAT(CONCAT("Deleted/", d.id), "-"), d.filename) where status_id=3;
