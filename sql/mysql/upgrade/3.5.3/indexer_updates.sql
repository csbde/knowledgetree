update mime_types set extractor_id = null;
delete from mime_extractors;
delete from system_settings where name='mimeTypesRegistered';