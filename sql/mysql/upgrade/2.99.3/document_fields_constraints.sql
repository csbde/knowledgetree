SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `document_fields_link` ADD INDEX `document_id` (`document_id`);
ALTER TABLE `document_fields_link` ADD INDEX `document_field_id` (`document_field_id`);
ALTER TABLE `document_fields_link` ADD CONSTRAINT `document_fields_link_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;
ALTER TABLE `document_fields_link` ADD CONSTRAINT `document_fields_link_ibfk_2` FOREIGN KEY (`document_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
