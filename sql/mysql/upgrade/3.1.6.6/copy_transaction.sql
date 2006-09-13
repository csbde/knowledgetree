TRUNCATE `zseq_document_transaction_types_lookup`;
INSERT INTO `zseq_document_transaction_types_lookup` SELECT MAX(`id`) FROM `document_transaction_types_lookup`;
SELECT @foo:=id + 1 FROM `zseq_document_transaction_types_lookup`;

INSERT INTO document_transaction_types_lookup VALUES (@foo, 'Copy', 'ktcore.transactions.copy');

TRUNCATE `zseq_document_transaction_types_lookup`;
INSERT INTO `zseq_document_transaction_types_lookup` SELECT MAX(`id`) FROM `document_transaction_types_lookup`;
