alter table `download_files` change `document_id`  `document_id` int NOT NULL;
alter table `folders` change `owner_id`  `owner_id`  int  NULL;
alter table `index_files` change `document_id`  `document_id`  int NOT NULL;
alter table `index_files` change `user_id`  `user_id`  int NOT NULL;
alter table `type_workflow_map` change `workflow_id`  `workflow_id` int  NULL;
