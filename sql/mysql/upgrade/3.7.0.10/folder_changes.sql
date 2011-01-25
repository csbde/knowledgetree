ALTER TABLE document_transactions ADD COLUMN parent_id INTEGER NOT NULL;

ALTER TABLE folder_transactions ADD COLUMN parent_id INTEGER NOT NULL;