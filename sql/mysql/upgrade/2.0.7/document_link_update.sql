ALTER TABLE `document_link_types` ADD `reverse_name` CHAR( 100 ) NOT NULL AFTER `name` ;
UPDATE `document_link_types` SET `reverse_name` = CONCAT(`name`, " (reverse)") WHERE `reverse_name` = "";
INSERT INTO `document_link_types` ( `id` , `name` , `reverse_name` , `description` ) VALUES ( '-1', 'depended on', 'was depended on by', 'Depends relationship whereby one documents depends on another''s creation to go through approval');
