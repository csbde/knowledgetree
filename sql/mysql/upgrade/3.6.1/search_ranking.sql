UPDATE `search_ranking` SET ranking = 300 WHERE groupname = 'document_metadata_version' AND itemname = 'name';
INSERT INTO `search_ranking` VALUES ('document_fields_link','value',1,'T');