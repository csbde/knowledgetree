SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `document_fields_link` DROP FOREIGN KEY `document_fields_link_ibfk_1`;
ALTER TABLE document_fields_link DROP COLUMN document_id;
SET FOREIGN_KEY_CHECKS=1;
