CREATE TABLE `folder_descendants`
(
	`parent_id` int(11) NOT NULL,
    `folder_id` int(11) NOT NULL,
    primary key (parent_id,folder_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table folder_descendants add foreign key(parent_id) references folders(id);
alter table folder_descendants add foreign key(folder_id) references folders(id);