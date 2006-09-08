ALTER TABLE `users` ADD `disabled` TINYINT(1) NOT NULL;
ALTER TABLE `users` ADD INDEX (`disabled`);
