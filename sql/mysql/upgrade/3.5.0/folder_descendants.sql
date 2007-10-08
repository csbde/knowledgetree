CREATE TABLE `folder_descendants`
(
	`parent_id` int(11) NOT NULL,
    `folder_id` int(11) NOT NULL,
    primary key (parent_id,folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;