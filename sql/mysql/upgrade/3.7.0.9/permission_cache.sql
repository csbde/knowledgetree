create table `permission_fast_cache` (
    `user_id` INT(11) NOT NULL,
    `descriptor_id` INT(11) NOT NULL,
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;