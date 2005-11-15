ALTER TABLE `document_transaction_types_lookup` ADD `namespace` VARCHAR(250) NOT NULL;
ALTER TABLE `document_transaction_types_lookup` ADD INDEX (`namespace`);
UPDATE `document_transaction_types_lookup` SET namespace = CONCAT("ktcore.transactions.", REPLACE(LCASE(name), " ", "_"));
TRUNCATE `zseq_document_transaction_types_lookup`;
INSERT INTO `zseq_document_transaction_types_lookup` SELECT MAX(`id`) FROM `document_transaction_types_lookup`;
SELECT @foo:=id + 1 FROM `zseq_document_transaction_types_lookup`;
INSERT INTO `document_transaction_types_lookup` VALUES (@foo, "Workflow state transition", "ktcore.transactions.workflow_state_transition");
TRUNCATE `zseq_document_transaction_types_lookup`;
INSERT INTO `zseq_document_transaction_types_lookup` SELECT MAX(`id`) FROM `document_transaction_types_lookup`;
