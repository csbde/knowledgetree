SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `document_metadata_version` ADD COLUMN `workflow_id` int(11) default NULL;
ALTER TABLE `document_metadata_version` ADD COLUMN `workflow_state_id` int(11) default NULL;
ALTER TABLE `document_metadata_version` ADD INDEX `workflow_id` (`workflow_id`);
ALTER TABLE `document_metadata_version` ADD INDEX `workflow_state_id` (`workflow_state_id`);

ALTER TABLE `document_metadata_version` ADD CONSTRAINT `document_metadata_version_ibfk_8` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`);
ALTER TABLE `document_metadata_version` ADD CONSTRAINT `document_metadata_version_ibfk_9` FOREIGN KEY (`workflow_state_id`) REFERENCES `workflow_states` (`id`);

UPDATE document_metadata_version AS DC, workflow_documents AS WC, documents AS D SET DC.workflow_id = WC.workflow_id, DC.workflow_state_id = WC.state_id WHERE DC.document_id = WC.document_id AND DC.id = D.metadata_version_id;
SET FOREIGN_KEY_CHECKS=1;
