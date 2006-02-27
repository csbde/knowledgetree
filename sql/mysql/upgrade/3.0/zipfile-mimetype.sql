TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;
SELECT @foo:=id + 1 FROM `zseq_mime_types`;
INSERT INTO `mime_types` VALUES (@foo, 'zip', 'application/x-zip', 'compressed', 'ZIP Compressed File');
SELECT @foo:=id + 2 FROM `zseq_mime_types`;
INSERT INTO `mime_types` VALUES (@foo, 'csv', 'text/csv', 'spreadsheet', 'Comma delimited spreadsheet');

TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;

UPDATE mime_types SET friendly_name = 'Binary File' WHERE id IN (9);
UPDATE mime_types SET friendly_name = 'Acrobat PDF' WHERE id IN (68);
UPDATE mime_types SET friendly_name = 'Postscript Document' WHERE id IN (75);
UPDATE mime_types SET friendly_name = 'Postscript Document' WHERE id IN (1);
UPDATE mime_types SET friendly_name = 'Plain Text' WHERE id IN (5);