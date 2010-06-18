CREATE TABLE document_comments (
    `id` int NOT NULL AUTO_INCREMENT,
	`document_id` int NOT NULL,
	`user_id` int NOT NULL,
	`parent_id` int NOT NULL,
	`date_created` timestamp NOT NULL,
	`comment` text,
	PRIMARY KEY(`id`),
	INDEX(`parent_id`),
	CONSTRAINT `document_comments_idx_doc_id` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB CHARACTER SET=utf8;