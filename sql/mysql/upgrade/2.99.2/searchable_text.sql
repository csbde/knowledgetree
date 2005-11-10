CREATE TABLE `document_searchable_text` (
  `document_id` int(11) default NULL,
  `document_text` mediumtext,
  KEY `document_text_document_id_indx` (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) TYPE=MyISAM;

CREATE TABLE `document_transaction_text` (
  `document_id` int(11) default NULL,
  `document_text` mediumtext,
  KEY `document_text_document_id_indx` (`document_id`),
  FULLTEXT KEY `document_text` (`document_text`)
) TYPE=MyISAM;
