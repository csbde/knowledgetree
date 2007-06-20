CREATE TABLE `tag_words` (            
   `id` int(10) NOT NULL,     
   `tag` varchar(100) default NULL,    
   PRIMARY KEY  (`id`))
ENGINE=InnoDB DEFAULT CHARSET=utf8;  

CREATE TABLE `document_tags` (
   `document_id` int(10) NOT NULL, 
   `tag_id` int(10) NOT NULL,       
   PRIMARY KEY  (`document_id`,`tag_id`),
   CONSTRAINT fk_document_tags_document_id FOREIGN KEY (document_id) REFERENCES documents(id) ON UPDATE CASCADE ON DELETE CASCADE,
   CONSTRAINT fk_document_tags_tag_id FOREIGN KEY (tag_id) REFERENCES tag_words(id) ON UPDATE CASCADE ON DELETE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `zseq_document_tags` (                    
   `id` int(10) NOT NULL auto_increment,       
   PRIMARY KEY  (`id`))
ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
INSERT INTO `zseq_document_tags` (id) VALUES ('1');

CREATE TABLE `zseq_tag_words` (                         
   `id` int(10) NOT NULL auto_increment,        
   PRIMARY KEY  (`id`))
ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
INSERT INTO `zseq_tag_words` (id) VALUES ('1');

