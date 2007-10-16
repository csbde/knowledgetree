alter table `download_files` change `document_id`  `document_id` int NOT NULL;
alter table `folders` change `owner_id`  `owner_id`  int  NULL;
alter table `index_files` change `document_id`  `document_id`  int NOT NULL;
alter table `index_files` change `user_id`  `user_id`  int NOT NULL;
alter table `type_workflow_map` change `workflow_id`  `workflow_id` int  NULL;

alter table document_content_version change mime_id mime_id int null default 9;

alter table documents change owner_id owner_id int null;
alter table documents change creator_id creator_id int null;
alter table documents change modified_user_id modified_user_id int null;

alter table document_transactions change document_id document_id int null;
alter table document_transactions change user_id user_id int null;

alter table folder_transactions change folder_id folder_id int null;
alter table folder_transactions change user_id user_id int null;

alter table folders change parent_id parent_id int null;
update documents set owner_id=null where owner_id=0;
update folders set parent_id=null where parent_id=0;
alter table discussion_threads change first_comment_id first_comment_id int null;
alter table discussion_threads change last_comment_id last_comment_id int null;
