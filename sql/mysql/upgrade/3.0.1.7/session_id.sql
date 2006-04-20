SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `document_transactions` ADD COLUMN `session_id` int(11) default NULL;
ALTER TABLE `document_transactions` ADD INDEX `session_id` (`session_id`);
ALTER TABLE `folder_transactions` ADD COLUMN `session_id` int(11) default NULL;
ALTER TABLE `folder_transactions` ADD INDEX `session_id` (`session_id`);
ALTER TABLE `user_history` ADD COLUMN `session_id` int(11) default NULL;
ALTER TABLE `user_history` ADD INDEX `session_id` (`session_id`);
SET FOREIGN_KEY_CHECKS=1;
