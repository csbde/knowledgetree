SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `document_fields` ADD `description` TEXT NOT NULL default '';
ALTER TABLE `fieldsets` ADD `description` TEXT NOT NULL default '';

UPDATE `document_fields` SET `description` = 'The category to which the document belongs.' WHERE id = 1;
UPDATE `fieldsets` SET `description` = 'The category to which the document belongs.' WHERE id = 1;

SET FOREIGN_KEY_CHECKS=1;
