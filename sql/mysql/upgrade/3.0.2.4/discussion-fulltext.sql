CREATE TABLE `comment_searchable_text`(
    `comment_id` INT(11) NOT NULL DEFAULT 0,
    `body` TEXT,
    `document_id` INT(11) NOT NULL DEFAULT 0,
    UNIQUE KEY `id` (`comment_id`),
    FULLTEXT INDEX `comment_search_text` (`body`)
) Type=MyISAM;

INSERT INTO `comment_searchable_text` (comment_id, body, document_id)
    SELECT DC.id, CONCAT(DC.body, ' ', DC.subject), DT.document_id FROM
    discussion_comments AS DC INNER JOIN 
    discussion_threads AS DT ON (DC.thread_id = DT.id);