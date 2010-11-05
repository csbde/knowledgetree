CREATE TABLE `shared_content` (`user_id` INT NOT NULL, `object_id` INT NOT NULL, `permission` int(1) NOT NULL, `type` enum('folder', 'document') DEFAULT 'document', INDEX (`user_id`));
