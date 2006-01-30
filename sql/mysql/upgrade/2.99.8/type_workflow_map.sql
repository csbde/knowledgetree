CREATE TABLE `type_workflow_map` (
    `document_type_id` INT(11) NOT NULL DEFAULT 0 UNIQUE,
    PRIMARY KEY (`document_type_id`),
    `workflow_id` INT UNSIGNED             -- can be null.
    ) TYPE=InnoDB;
    
CREATE TABLE `folder_workflow_map` (
    `folder_id` INT(11) NOT NULL DEFAULT 0 UNIQUE,
    PRIMARY KEY (`folder_id`),
    `workflow_id` INT(11)             -- can be null.
    ) TYPE=InnoDB;