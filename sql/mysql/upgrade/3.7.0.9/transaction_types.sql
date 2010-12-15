INSERT INTO `document_transaction_types_lookup`
    (`id`, `name`, `namespace`)
VALUES
    (null, 'Share', 'ktcore.transactions.share'),
    (null, 'Ownership changed', 'ktcore.transactions.ownership_change'),
    (null, 'Subscribe', 'ktcore.transactions.subscribe'),
    (null, 'Unsubscribe','ktcore.transactions.unsubscribe');