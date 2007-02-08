SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `document_fields_link` TYPE=InnoDB;
ALTER TABLE `document_fields_link` ADD INDEX `document_id` (`document_id`);
ALTER TABLE `document_fields_link` ADD INDEX `document_field_id` (`document_field_id`);

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `document_fields_link` as dfl USING `document_fields_link` as dfl, documents
	WHERE not exists(select 1 from `documents` as d where dfl.document_id = d.id);        

ALTER TABLE `document_fields_link` ADD CONSTRAINT `document_fields_link_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

-- CLEAROUT ANY BROKEN RECORDS PRIOR TO ASSIGNING CONSTRAINT

DELETE FROM `document_fields_link` as dfl USING `document_fields_link` as dfl, document_fields
	WHERE not exists(select 1 from `document_fields` as df where dfl.document_field_id = df.id);  
        
ALTER TABLE `document_fields_link` ADD CONSTRAINT `document_fields_link_ibfk_2` FOREIGN KEY (`document_field_id`) REFERENCES `document_fields` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS=1;
