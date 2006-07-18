ALTER TABLE `documents` ADD `restore_folder_id` INT(11);
ALTER TABLE `documents` ADD `restore_folder_path` text;
ALTER TABLE `documents` CHANGE `folder_id` `folder_id` int(11),