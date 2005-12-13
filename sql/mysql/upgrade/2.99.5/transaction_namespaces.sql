SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `document_transactions` ADD `transaction_namespace` char(255) NOT NULL default 'ktcore.transactions.event';

UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.create' WHERE `transaction_id` = 1;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.update' WHERE `transaction_id` = 2;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.delete' WHERE `transaction_id` = 3;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.rename' WHERE `transaction_id` = 4;
UPDATE `document_transactions` SET `transaction_namespace` = 'tcore.transactions.move' WHERE `transaction_id` = 5;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.download' WHERE `transaction_id` = 6;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.check_in' WHERE `transaction_id` = 7;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.check_out' WHERE `transaction_id` = 8;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.collaboration_step_rollback' WHERE `transaction_id` = 9;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.view' WHERE `transaction_id` = 10;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.expunge' WHERE `transaction_id` = 11;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.force_checkin' WHERE `transaction_id` = 12;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.email_link' WHERE `transaction_id` = 13;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.collaboration_step_approve' WHERE `transaction_id` = 14;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.email_attachment' WHERE `transaction_id` = 15;
UPDATE `document_transactions` SET `transaction_namespace` = 'ktcore.transactions.workflow_state_transition' WHERE `transaction_id` = 16;

ALTER TABLE `document_transactions` DROP `transaction_id`;

SET FOREIGN_KEY_CHECKS=1;
