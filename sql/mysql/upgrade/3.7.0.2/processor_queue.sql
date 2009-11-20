--
-- Table structure for table `process_queue`
--

CREATE table `process_queue` (
  `document_id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `date_processed` timestamp,
  `status_msg` mediumtext,
  `process_type` varchar(20),
  PRIMARY KEY  (`document_id`),
  CONSTRAINT `process_queue_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
