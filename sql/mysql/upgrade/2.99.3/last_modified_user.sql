ALTER TABLE `documents` ADD `modified_user_id` INT NOT NULL ;
ALTER TABLE `documents` ADD INDEX ( `modified_user_id` ) ;
CREATE TEMPORARY TABLE `document_modified_user_id` AS
	SELECT
		D.id AS document_id, DT.user_id AS user_id 
	FROM
		documents AS D
		LEFT JOIN document_transactions AS DT 
			ON D.modified = DT.datetime AND 
			((D.id = DT.document_id) OR (D.live_document_id = DT.document_id)) AND
			(DT.transaction_id IN (1,7));
UPDATE `document_modified_user_id` AS DMUI, documents AS D
	SET DMUI.user_id = D.creator_id WHERE DMUI.user_id IS NULL AND DMUI.document_id = D.id;
UPDATE `documents` AS D, `document_modified_user_id` AS DMUI
	SET D.modified_user_id = DMUI.user_id WHERE D.id = DMUI.document_id AND D.modified_user_id = 0;
DROP TABLE `document_modified_user_id`;
