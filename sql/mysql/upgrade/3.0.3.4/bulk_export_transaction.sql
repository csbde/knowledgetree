TRUNCATE `zseq_document_transaction_types_lookup`;
INSERT INTO `zseq_document_transaction_types_lookup` SELECT MAX(`id`) FROM `document_transaction_types_lookup`;
SELECT @foo:=id + 1 FROM `zseq_document_transaction_types_lookup`;

INSERT INTO document_transaction_types_lookup VALUES (@foo, 'Bulk Export', 'ktstandard.transactions.bulk_export');

TRUNCATE `zseq_document_transaction_types_lookup`;
INSERT INTO `zseq_document_transaction_types_lookup` SELECT MAX(`id`) FROM `document_transaction_types_lookup`;
