ALTER TABLE `document_types_lookup` ADD COLUMN `disabled` tinyint(4) NOT NULL;
ALTER TABLE `document_types_lookup` ADD INDEX `disabled` (`disabled`);
