ALTER TABLE document_transactions ADD COLUMN parent_id INT(11);

ALTER TABLE folder_transactions ADD COLUMN parent_id INT(11);