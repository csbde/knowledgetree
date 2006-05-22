CREATE TABLE folder_searchable_text (
    `folder_id`   INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY(folder_id),
    `folder_text` text,
    KEY `folder_searchable_text_folder_indx` (`folder_id`),
    FULLTEXT KEY `folder_text` (`folder_text`)
) Type=MyISAM;

-- generate the data

insert into folder_searchable_text (folder_id, folder_text) SELECT id, name from folders;
