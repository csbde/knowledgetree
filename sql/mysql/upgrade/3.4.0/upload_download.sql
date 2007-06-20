CREATE TABLE `uploaded_files` (                                                           
`tempfilename` varchar(100) NOT NULL,                                                   
`filename` varchar(100) NOT NULL,                                                       
`userid` int(11) NOT NULL,                                                              
`uploaddate` timestamp NOT NULL,  
`action` char(1) NOT NULL COMMENT 'A = Add, C = Checkin',                         
`document_id` int(11) default NULL                                                      
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `download_files` (                              
`document_id` int(10) unsigned NOT NULL,                   
`session` varchar(100) NOT NULL,                           
`download_date` timestamp NULL default CURRENT_TIMESTAMP,  
`downloaded` int(10) unsigned NOT NULL default '0',        
`filesize` int(10) unsigned NOT NULL,                      
`content_version` int(10) unsigned NOT NULL,               
`hash` varchar(100) NOT NULL,                              
PRIMARY KEY  (`document_id`,`session`)                     
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `index_files` (
`document_id` int(10) unsigned NOT NULL,                                               
`user_id` int(10) unsigned NOT NULL,                                                   
`indexdate` timestamp NOT NULL,  
PRIMARY KEY  (`document_id`)                                                           
) ENGINE=InnoDB DEFAULT CHARSET=utf8;    