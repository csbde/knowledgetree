ALTER TABLE plugins ADD COLUMN `orderby` int(11) NOT NULL default '0';
UPDATE plugins SET orderby = -75 WHERE namespace = 'ktcore.language.plugin';
UPDATE plugins SET orderby = -25 WHERE namespace = 'ktcore.plugin';