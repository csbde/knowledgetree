ALTER TABLE mime_types ADD friendly_name CHAR (255) default '';

UPDATE mime_types SET friendly_name = 'OpenOffice.org File' WHERE id IN (130,131,132,133,134,135,136,137,138,139);