INSERT INTO `document_transaction_types_lookup`
    (`id`, `name`, `namespace`)
VALUES
    (null, 'Share', 'ktcore.transactions.share'),
    (null, 'Ownership changed', 'ktcore.transactions.ownership_change'),
    (null, 'User subscribed to document', 'ktcore.transactions.subscribe'),
    (null, 'User unsubscribed from document','ktcore.transactions.unsubscribe');