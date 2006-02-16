ALTER TABLE mime_types ADD friendly_name CHAR (255) default '';

-- friendly name definitions

UPDATE mime_types SET friendly_name = 'OpenOffice.org File' WHERE id IN (130,131,132,133,134,135,136,137,138,139);
UPDATE mime_types SET friendly_name = 'JPEG Image' WHERE id IN (37,38,39);
UPDATE mime_types SET friendly_name = 'JPEG Image' WHERE id IN (71);
UPDATE mime_types SET friendly_name = 'BMP Image' WHERE id IN (10);
UPDATE mime_types SET friendly_name = 'GIF Image' WHERE id IN (27);
UPDATE mime_types SET friendly_name = 'TIFF Image' WHERE id IN (110,111);
UPDATE mime_types SET friendly_name = 'HTML Webpage' WHERE id IN (31,32);
UPDATE mime_types SET friendly_name = 'Tar or Compressed Tar File' WHERE id IN (105,129);
UPDATE mime_types SET friendly_name = 'Acrobar PDF or Postscript Document' WHERE id IN (75,68);
UPDATE mime_types SET friendly_name = 'Excel Spreadsheet' WHERE id IN (122);
UPDATE mime_types SET friendly_name = 'Excel Template' WHERE id IN (140);
UPDATE mime_types SET friendly_name = 'Word Template' WHERE id IN (141);
UPDATE mime_types SET friendly_name = 'Word Document' WHERE id IN (20);
UPDATE mime_types SET friendly_name = 'Powerpoint Presentation' WHERE id IN (74);
UPDATE mime_types SET friendly_name = 'ZIP Compressed File' WHERE id IN (127);
UPDATE mime_types SET friendly_name = 'GZIP Compressed File' WHERE id IN (128);
UPDATE mime_types SET friendly_name = 'Plain Text' WHERE id IN (114);
UPDATE mime_types SET friendly_name = 'Access Database' WHERE id IN (46,47);
UPDATE mime_types SET friendly_name = 'Encapsulated Postscript' WHERE id IN (23);

-- a million kinds of video

UPDATE mime_types SET friendly_name = 'Video File' WHERE id IN (7,53,54,57,58,59,76);

-- Openoffice

UPDATE mime_types SET friendly_name = 'OpenOffice.org Presentation' WHERE id IN (136);
UPDATE mime_types SET friendly_name = 'OpenOffice.org Spreadsheet' WHERE id IN (132);
UPDATE mime_types SET friendly_name = 'OpenOffice.org Writer Document' WHERE id IN (130);
UPDATE mime_types SET friendly_name = 'OpenOffice.org Presentation' WHERE id IN (136);


-- add some new ones.

TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;
SELECT @foo:=id + 1 FROM `zseq_mime_types`;
INSERT INTO `mime_types` VALUES (@foo, "bz2", "application/x-bzip2",'compressed','BZIP2 Compressed File');
SELECT @foo:=id + 2 FROM `zseq_mime_types`;
INSERT INTO `mime_types` VALUES (@foo, "diff", "text/plain",'text','Source Diff File');
SELECT @foo:=id + 3 FROM `zseq_mime_types`;
INSERT INTO `mime_types` VALUES (@foo, "patch", "text/plain",'text','Patch File');
TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;



