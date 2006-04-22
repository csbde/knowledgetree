ALTER TABLE `folder_transactions` ADD admin_mode TINYINT(1) NOT NULL default 0;
ALTER TABLE `document_transactions` ADD admin_mode TINYINT(1) NOT NULL default 0;