CREATE TABLE IF NOT EXISTS `ratingcontent_document` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`document_id` INT UNSIGNED NOT NULL ,
`user_id` INT UNSIGNED NOT NULL ,
`date_time` DATETIME NOT NULL ,
INDEX (  `document_id` ,  `user_id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;