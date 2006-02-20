TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;

SELECT @foo:=id FROM `zseq_mime_types`;

INSERT INTO `mime_types` VALUES (@foo + 1, "odt", "application/vnd.oasis.opendocument.text", 'opendocument','OpenDocument Text');
INSERT INTO `mime_types` VALUES (@foo + 2, "ott", "application/vnd.oasis.opendocument.text-template", 'opendocument','OpenDocument Text Template');
INSERT INTO `mime_types` VALUES (@foo + 3, "oth", "application/vnd.oasis.opendocument.text-web", 'opendocument', 'HTML Document Template');
INSERT INTO `mime_types` VALUES (@foo + 4, "odm", "application/vnd.oasis.opendocument.text-master", 'opendocument', 'OpenDocument Master Document');
INSERT INTO `mime_types` VALUES (@foo + 5, "odg", "application/vnd.oasis.opendocument.graphics", 'opendocument', 'OpenDocument Drawing');
INSERT INTO `mime_types` VALUES (@foo + 6, "otg", "application/vnd.oasis.opendocument.graphics-template", 'opendocument', 'OpenDocument Drawing Template');
INSERT INTO `mime_types` VALUES (@foo + 7, "odp", "application/vnd.oasis.opendocument.presentation", 'opendocument', 'OpenDocument Presentation');
INSERT INTO `mime_types` VALUES (@foo + 8, "otp", "application/vnd.oasis.opendocument.presentation-template", 'opendocument', 'OpenDocument Presentation Template');
INSERT INTO `mime_types` VALUES (@foo + 9, "ods", "application/vnd.oasis.opendocument.spreadsheet", 'opendocument', 'OpenDocument Spreadsheet');
INSERT INTO `mime_types` VALUES (@foo + 10, "ots", "application/vnd.oasis.opendocument.spreadsheet-template", 'opendocument', 'OpenDocument Spreadsheet Template');
INSERT INTO `mime_types` VALUES (@foo + 11, "odc", "application/vnd.oasis.opendocument.chart", 'opendocument', 'OpenDocument Chart');
INSERT INTO `mime_types` VALUES (@foo + 12, "odf", "application/vnd.oasis.opendocument.formula", 'opendocument', 'OpenDocument Formula');
INSERT INTO `mime_types` VALUES (@foo + 13, "odb", "application/vnd.oasis.opendocument.database", 'opendocument', 'OpenDocument Database');
INSERT INTO `mime_types` VALUES (@foo + 14, "odi", "application/vnd.oasis.opendocument.image", 'opendocument', 'OpenDocument Image');

TRUNCATE `zseq_mime_types`;
INSERT INTO `zseq_mime_types` SELECT MAX(`id`) FROM `mime_types`;

