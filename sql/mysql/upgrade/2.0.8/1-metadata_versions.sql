ALTER TABLE `documents` ADD `live_document_id` INT;
ALTER TABLE `documents` ADD INDEX ( `live_document_id` ) ;
ALTER TABLE `documents` ADD `metadata` INT DEFAULT '0' NOT NULL ;
