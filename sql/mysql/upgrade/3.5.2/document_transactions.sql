alter table document_transactions change version version varchar(50);
alter table document_transactions change ip ip varchar(15);
alter table document_transactions change filename filename mediumtext;
alter table document_transactions change comment comment mediumtext;
alter table document_transactions change transaction_namespace transaction_namespace varchar(255);
alter table document_transactions add index (`datetime`,`transaction_namespace`);