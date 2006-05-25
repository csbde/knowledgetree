TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;
SELECT @foo:=id + 1 FROM `zseq_mime_types`;
INSERT INTO `mime_types` VALUES (@foo, 'msi', 'application/msword', 'compressed', 'MSI Installer file');

TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;
