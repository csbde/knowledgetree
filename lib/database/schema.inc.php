<?php

/**
 * $Id: $
 *
 * Database access utility class
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

class KTSchemaUtil
{
	/**
	 * Indicates if statements to the database must be performed.
	 *
	 * @var boolean
	 */
	public $persist;

	/**
	 * Primary key definitions.
	 *
	 * @var array
	 */
	private $primaryKeys;

	/**
	 * Foreign key definitions
	 *
	 * @var array
	 */
	private $foreignKeys;

	/**
	 * Index definitions
	 *
	 * @var array
	 */
	private $indexes;

	/**
	 * Schema of database
	 *
	 * @var array
	 */
	private $schema;

	private $primary;


	private function __construct($setup=false)
	{
		$this->persist = true;

		$this->getDBSchema();
		if ($setup)
		{
			$this->setupAdminDatabase();
		}

		$this->definePrimaryKeys();
		$this->defineForeignKeys();
		$this->defineOtherIndexes();
	}

	public function setTablesToInnoDb()
	{
		foreach($this->schema as $tablename=>$schema)
		{
			$schema = strtolower($schema);

			$isInnoDb = (strpos($schema, 'innodb') !== false);
			$hasFulltext = (strpos($schema, 'fulltext') !== false);

			// if the table is innodb already, don't have to do anything
			// only myisam tables can do fulltext

			if (!$isInnoDb && !$hasFulltext)
			{
				$sql = "ALTER TABLE $tablename TYPE=innodb;";
				$this->_exec($sql);
			}
		}
	}


	private function createFixUser()
	{
		$sql = "SELECT 1 FROM users WHERE id = -10;";
		$rs = DBUtil::getResultArray($sql);
		if (PEAR::isError($rs))
		{
			print '';
		}
		if (count($rs) == 0)
		{
			$sql = "INSERT INTO users (id,username,name,password,max_sessions) VALUES (-10,'_deleted_helper','Deleted User','---------------',0)";
			$this->_exec($sql);
		}
	}

	public function setupAdminDatabase()
	{
		global $default;
		$dsn = array(
			'phptype'  => $default->dbType,
			'username' => $default->dbAdminUser,
			'password' => $default->dbAdminPass,
			'hostspec' => $default->dbHost,
			'database' => $default->dbName,
			'port' => $default->dbPort,
		);

		$options = array(
				'debug'       => 2,
				'portability' => DB_PORTABILITY_ERRORS,
				'seqname_format' => 'zseq_%s',
			);

		$default->_admindb = &DB::connect($dsn, $options);
		if (PEAR::isError($default->_admindb))
		{
			die($default->_admindb->toString());
		}
		$default->_admindb->setFetchMode(DB_FETCHMODE_ASSOC);
		return;
	}

	private function removeDuplicateIndexes()
	{
		foreach($this->primary as $table=>$key)
		{
			$this->dropIndex($table,$key);
		}

	}


	/**
	 * Enter description here...
	 *
	 * @return KTSchemaUtil
	 */
	public function getSingleton()
	{
		static $singleton = null;
		if (is_null($singleton))
		{
			$singleton = new KTSchemaUtil();
		}
		return $singleton;
	}

	/**
	 * Adds primary keys to the database
	 *
	 */
	private function definePrimaryKeys()
	{
		$this->primaryKeys = array();
		$this->definePrimaryKey('active_sessions', 'id');
		$this->definePrimaryKey('archive_restoration_request','id');
		$this->definePrimaryKey('archiving_settings','id');
		$this->definePrimaryKey('archiving_type_lookup','id');
		$this->definePrimaryKey('authentication_sources','id');
		$this->definePrimaryKey('baobab_keys','id');
		$this->definePrimaryKey('baobab_user_keys','id');
		$this->definePrimaryKey('column_entries','id');
		$this->definePrimaryKey('comment_searchable_text','comment_id');
		$this->definePrimaryKey('dashlet_disables','id');
		$this->definePrimaryKey('data_types','id');
		$this->definePrimaryKey('discussion_comments','id');
		$this->definePrimaryKey('discussion_threads','id');
		$this->definePrimaryKey('document_archiving_link','id');
		$this->definePrimaryKey('document_content_version','id');
		$this->definePrimaryKey('document_fields','id');
		$this->definePrimaryKey('document_fields_link','id');
		$this->definePrimaryKey('document_incomplete','id');
		$this->definePrimaryKey('document_link','id');
		$this->definePrimaryKey('document_link_types','id');
		$this->definePrimaryKey('document_metadata_version','id');
		$this->definePrimaryKey('document_role_allocations','id');
		$this->definePrimaryKey('document_subscriptions','id');
		$this->definePrimaryKey('document_tags',array('document_id','tag_id'));
		$this->definePrimaryKey('document_text', 'document_id');
		$this->definePrimaryKey('document_transaction_types_lookup', 'id');
		$this->definePrimaryKey('document_transaction_text', 'document_id');
		$this->definePrimaryKey('document_transactions','id');
		$this->definePrimaryKey('document_type_fields_link','id');
		$this->definePrimaryKey('document_type_fieldsets_link','id');
		$this->definePrimaryKey('document_types_lookup','id');
		$this->definePrimaryKey('documents','id');
		$this->definePrimaryKey('download_files',array('document_id','session'));
		$this->definePrimaryKey('field_behaviours','id');
		$this->definePrimaryKey('field_value_instances','id');
		$this->definePrimaryKey('fieldsets','id');
		$this->definePrimaryKey('folder_doctypes_link','id');
		$this->definePrimaryKey('folder_searchable_text','folder_id');
		$this->definePrimaryKey('folder_subscriptions','id');
		$this->definePrimaryKey('folder_transactions','id');
		$this->definePrimaryKey('folder_workflow_map','folder_id');
		$this->definePrimaryKey('folders','id');
		$this->definePrimaryKey('folders_users_roles_link','id');
		$this->definePrimaryKey('groups_groups_link','id');
		$this->definePrimaryKey('groups_lookup','id');
		$this->definePrimaryKey('help','id');
		$this->definePrimaryKey('help_replacement','id');
		$this->definePrimaryKey('index_files','document_id');
		$this->definePrimaryKey('interceptor_instances','id');
		$this->definePrimaryKey('links','id');
		$this->definePrimaryKey('metadata_lookup','id');
		$this->definePrimaryKey('metadata_lookup_tree','id');
		$this->definePrimaryKey('mime_documents','id');
		$this->definePrimaryKey('mime_extractors','id');
		$this->definePrimaryKey('mime_document_mapping',array('mime_type_id','mime_document_id'));
		$this->definePrimaryKey('mime_types','id');
		$this->definePrimaryKey('news','id');
		$this->definePrimaryKey('notifications','id');
		$this->definePrimaryKey('organisations_lookup','id');
		$this->definePrimaryKey('permission_assignments','id');
		$this->definePrimaryKey('permission_descriptor_groups', array('descriptor_id','group_id'));
		$this->definePrimaryKey('permission_descriptor_roles', array('descriptor_id','role_id'));
		$this->definePrimaryKey('permission_descriptor_users', array('descriptor_id','user_id'));
		$this->definePrimaryKey('permission_descriptors','id');
		$this->definePrimaryKey('permission_dynamic_conditions','id');
		$this->definePrimaryKey('permission_lookup_assignments','id');
		$this->definePrimaryKey('permission_lookups','id');
		$this->definePrimaryKey('permission_objects','id');
		$this->definePrimaryKey('permissions','id');
		$this->definePrimaryKey('plugin_rss','id');
		$this->definePrimaryKey('plugins','id');
		$this->definePrimaryKey('plugin_helper','id');
		$this->definePrimaryKey('quicklinks','id');
		$this->definePrimaryKey('role_allocations','id');
		$this->definePrimaryKey('roles','id');
		$this->definePrimaryKey('saved_searches','id');
		$this->definePrimaryKey('scheduler_tasks','id');
		$this->definePrimaryKey('search_ranking',array('groupname','itemname'));
		$this->definePrimaryKey('search_saved','id');
		$this->definePrimaryKey('search_saved_events','document_id');
		$this->definePrimaryKey('status_lookup','id');
		$this->definePrimaryKey('system_settings','id');
		$this->definePrimaryKey('tag_words','id');
		$this->definePrimaryKey('time_period','id');
		$this->definePrimaryKey('time_unit_lookup','id');
		$this->definePrimaryKey('trigger_selection','event_ns');
		$this->definePrimaryKey('type_workflow_map','document_type_id');
		$this->definePrimaryKey('units_lookup','id');
		$this->definePrimaryKey('units_organisations_link','id');
		$this->definePrimaryKey('upgrades','id');
		$this->definePrimaryKey('uploaded_files','tempfilename');
		$this->definePrimaryKey('user_history','id');
		$this->definePrimaryKey('user_history_documents','id');
		$this->definePrimaryKey('user_history_folders','id');
		$this->definePrimaryKey('users','id');
		$this->definePrimaryKey('users_groups_link','id');
		$this->definePrimaryKey('workflow_actions','workflow_id');
		$this->definePrimaryKey('workflow_documents','document_id');
		$this->definePrimaryKey('workflow_state_permission_assignments','id');
		$this->definePrimaryKey('workflow_states','id');
		$this->definePrimaryKey('workflow_state_transitions',array('state_id','transition_id'));
		$this->definePrimaryKey('workflow_transitions','id');
		$this->definePrimaryKey('workflow_trigger_instances','id');
		$this->definePrimaryKey('workflows','id');
	}

	/**
	 * Adds foreign keys to the database
	 *
	 */
	private function defineForeignKeys()
	{
		$this->foreignKeys = array();
		$this->defineForeignKey('active_sessions', 'user_id', 'users', 'id');

		$this->defineForeignKey('archive_restoration_request', 'document_id', 'documents', 'id');
		$this->defineForeignKey('archive_restoration_request', 'request_user_id', 'users', 'id');
		$this->defineForeignKey('archive_restoration_request', 'admin_user_id', 'users', 'id');

		$this->defineForeignKey('archiving_settings', 'archiving_type_id', 'archiving_type_lookup', 'id');
		$this->defineForeignKey('archiving_settings', 'time_period_id', 'time_period', 'id');

		$this->defineForeignKey('baobab_user_keys', 'user_id', 'users', 'id');
		$this->defineForeignKey('baobab_user_keys', 'key_id', 'baobab_keys', 'id');

		$this->defineForeignKey('comment_searchable_text', 'comment_id', 'discussion_comments', 'id');
		$this->defineForeignKey('comment_searchable_text', 'document_id', 'documents', 'id');

		$this->defineForeignKey('dashlet_disables', 'user_id', 'users', 'id');

		$this->defineForeignKey('discussion_comments', 'thread_id', 'discussion_threads', 'id');
		$this->defineForeignKey('discussion_comments', 'user_id', 'users', 'id');
		$this->defineForeignKey('discussion_comments', 'in_reply_to', 'discussion_comments', 'id');

		$this->defineForeignKey('discussion_threads', 'document_id', 'documents', 'id');
		$this->defineForeignKey('discussion_threads', 'first_comment_id', 'discussion_comments', 'id');
		$this->defineForeignKey('discussion_threads', 'last_comment_id', 'discussion_comments', 'id');
		$this->defineForeignKey('discussion_threads', 'creator_id', 'users', 'id');

		$this->defineForeignKey('document_archiving_link', 'document_id', 'documents', 'id');
		$this->defineForeignKey('document_archiving_link', 'archiving_settings_id', 'archiving_settings', 'id');

		$this->defineForeignKey('document_content_version', 'document_id', 'documents', 'id');
		$this->defineForeignKey('document_content_version', 'mime_id', 'mime_types', 'id');

		$this->defineForeignKey('document_fields','parent_fieldset','fieldsets','id');

		$this->defineForeignKey('document_fields_link','document_field_id','document_fields','id');
		$this->defineForeignKey('document_fields_link','metadata_version_id','document_metadata_version','id');

		$this->defineForeignKey('document_link','parent_document_id', 'documents', 'id');
		$this->defineForeignKey('document_link','child_document_id', 'documents', 'id');
		$this->defineForeignKey('document_link','link_type_id','document_link_types','id');

		$this->defineForeignKey('document_metadata_version','document_type_id','document_types_lookup','id');
		$this->defineForeignKey('document_metadata_version','status_id','status_lookup','id');
		$this->defineForeignKey('document_metadata_version','document_id','documents','id');
		$this->defineForeignKey('document_metadata_version','version_creator_id','users','id');
		$this->defineForeignKey('document_metadata_version','content_version_id','document_content_version','id');
		$this->defineForeignKey('document_metadata_version','workflow_id','workflows','id');
		$this->defineForeignKey('document_metadata_version','workflow_state_id','workflow_states','id');

		$this->defineForeignKey('document_role_allocations','role_id','roles','id');
		$this->defineForeignKey('document_role_allocations','permission_descriptor_id','permission_descriptors','id');

		$this->defineForeignKey('document_searchable_text','document_id','documents','id');

		$this->defineForeignKey('document_subscriptions','user_id','users','id');
		$this->defineForeignKey('document_subscriptions','document_id','documents','id');

		$this->defineForeignKey('document_tags','document_id','documents','id');
		$this->defineForeignKey('document_tags','tag_id','tag_words','id');

		$this->defineForeignKey('document_text','document_id','documents','id');


		$this->defineForeignKey('document_transaction_text','document_id','documents','id');

		$this->defineForeignKey('document_type_fields_link','document_type_id', 'document_types_lookup','id');
		$this->defineForeignKey('document_type_fields_link','field_id','document_fields','id');

		$this->defineForeignKey('document_type_fieldsets_link','document_type_id', 'document_types_lookup','id');
		$this->defineForeignKey('document_type_fieldsets_link','fieldset_id','fieldsets','id');

		$this->defineForeignKey('documents','creator_id','users','id', 'SET NULL', 'SET NULL');
		$this->defineForeignKey('documents','folder_id','folders','id');
		$this->defineForeignKey('documents','checked_out_user_id','users','id', 'SET NULL', 'SET NULL');
		$this->defineForeignKey('documents','status_id','status_lookup','id');
		$this->defineForeignKey('documents','permission_object_id','permission_objects','id');
		$this->defineForeignKey('documents','permission_lookup_id','permission_lookups','id');
		$this->defineForeignKey('documents','modified_user_id','users','id', 'SET NULL', 'SET NULL');
		$this->defineForeignKey('documents','metadata_version_id','document_metadata_version','id');

		$this->defineForeignKey('download_files','document_id','documents','id');

		$this->defineForeignKey('field_behaviour_options','behaviour_id','field_behaviours','id');
		$this->defineForeignKey('field_behaviour_options','field_id','document_fields','id');
		$this->defineForeignKey('field_behaviour_options','instance_id','field_value_instances','id');

		$this->defineForeignKey('field_behaviours','field_id','document_fields','id');

		$this->defineForeignKey('field_orders','child_field_id','document_fields','id');
		$this->defineForeignKey('field_orders','parent_field_id','document_fields','id');
		$this->defineForeignKey('field_orders','fieldset_id','fieldsets','id');

		$this->defineForeignKey('field_value_instances','field_value_id','metadata_lookup','id'); // it is so.. strange ;)
		$this->defineForeignKey('field_value_instances','behaviour_id','field_behaviours','id');
		$this->defineForeignKey('field_value_instances','field_id','document_fields','id');

		$this->defineForeignKey('fieldsets','master_field','document_fields','id');

		$this->defineForeignKey('folder_descendants','parent_id','folders','id');
		$this->defineForeignKey('folder_descendants','folder_id','folders','id');

		$this->defineForeignKey('folder_doctypes_link','folder_id','folders','id');
		$this->defineForeignKey('folder_doctypes_link','document_type_id','document_types_lookup','id');

		$this->defineForeignKey('folder_searchable_text','folder_id','folders','id');

		$this->defineForeignKey('folder_subscriptions','user_id','users','id');
		$this->defineForeignKey('folder_subscriptions','folder_id','folders','id');

		$this->defineForeignKey('folder_workflow_map','folder_id', 'folders','id');
		$this->defineForeignKey('folder_workflow_map','workflow_id', 'workflows','id');

		$this->defineForeignKey('folders','creator_id','users','id');
		$this->defineForeignKey('folders','permission_object_id','permission_objects','id');
		$this->defineForeignKey('folders','permission_lookup_id','permission_lookups','id');
		$this->defineForeignKey('folders','parent_id','folders','id');

		$this->defineForeignKey('folders_users_roles_link','user_id','users','id');
		$this->defineForeignKey('folders_users_roles_link','document_id','documents','id');

		$this->defineForeignKey('groups_groups_link','parent_group_id','groups_lookup','id');
		$this->defineForeignKey('groups_groups_link','member_group_id','groups_lookup','id');

		$this->defineForeignKey('groups_lookup','unit_id', 'units_lookup','id');

		$this->defineForeignKey('index_files','document_id','documents','id');
		$this->defineForeignKey('index_files','user_id','users','id');

		$this->defineForeignKey('metadata_lookup','document_field_id','document_fields','id');

		$this->defineForeignKey('metadata_lookup_tree','document_field_id', 'document_fields','id');

		$this->defineForeignKey('mime_types','mime_document_id','mime_documents','id', 'set null', 'set null');
		$this->defineForeignKey('mime_types','extractor_id','mime_extractors','id', 'set null', 'set null');

		$this->defineForeignKey('mime_document_mapping','mime_type_id','mime_types','id');
		$this->defineForeignKey('mime_document_mapping','mime_document_id','mime_documents','id');

		$this->defineForeignKey('news','image_mime_type_id','mime_types','id');

		$this->defineForeignKey('notifications','user_id', 'users','id');

		$this->defineForeignKey('permission_assignments','permission_id', 'permissions','id');
		$this->defineForeignKey('permission_assignments','permission_object_id','permission_objects','id'); // duplicate
		$this->defineForeignKey('permission_assignments','permission_descriptor_id','permission_descriptors','id');

		$this->defineForeignKey('permission_descriptor_groups','descriptor_id','permission_descriptors','id');
		$this->defineForeignKey('permission_descriptor_groups','group_id','groups_lookup','id');

		$this->defineForeignKey('permission_descriptor_roles','descriptor_id','permission_descriptors','id');
		$this->defineForeignKey('permission_descriptor_roles','role_id','roles','id');

		$this->defineForeignKey('permission_descriptor_users','descriptor_id','permission_descriptors','id');
		$this->defineForeignKey('permission_descriptor_users','user_id','users','id');

		$this->defineForeignKey('permission_dynamic_assignments','dynamic_condition_id','permission_dynamic_conditions','id');
		$this->defineForeignKey('permission_dynamic_assignments','permission_id','permissions','id');

		$this->defineForeignKey('permission_dynamic_conditions','permission_object_id','permission_objects','id');
		$this->defineForeignKey('permission_dynamic_conditions','group_id','groups_lookup','id');
		$this->defineForeignKey('permission_dynamic_conditions','condition_id','saved_searches','id');

		$this->defineForeignKey('permission_lookup_assignments','permission_id','permissions','id');
		$this->defineForeignKey('permission_lookup_assignments','permission_lookup_id','permission_lookups','id'); // duplicate
		$this->defineForeignKey('permission_lookup_assignments','permission_descriptor_id','permission_descriptors','id');

		$this->defineForeignKey('plugin_rss','user_id','users','id');

		$this->defineForeignKey('quicklinks','user_id','users','id');

		$this->defineForeignKey('role_allocations','folder_id','folders','id');
		$this->defineForeignKey('role_allocations','role_id', 'roles','id');
		$this->defineForeignKey('role_allocations','permission_descriptor_id','permission_descriptors','id');

		$this->defineForeignKey('saved_searches','user_id','users','id');

		$this->defineForeignKey('search_document_user_link','document_id','documents','id');
		$this->defineForeignKey('search_document_user_link','user_id','users','id');

	 	$this->defineForeignKey('search_saved','user_id','users','id');
	 	$this->defineForeignKey('search_saved_events','document_id','documents','id');

	 	$this->defineForeignKey('time_period','time_unit_id','time_unit_lookup','id');

	 	$this->defineForeignKey('type_workflow_map','document_type_id','document_types_lookup','id');
	 	$this->defineForeignKey('type_workflow_map','workflow_id','workflows','id');

		$this->defineForeignKey('units_lookup','folder_id','folders','id');

		$this->defineForeignKey('units_organisations_link','unit_id','units_lookup','id');
		$this->defineForeignKey('units_organisations_link','organisation_id','organisations_lookup','id');

		$this->defineForeignKey('uploaded_files','userid','users','id');
		$this->defineForeignKey('uploaded_files','document_id','documents','id');

		$this->defineForeignKey('user_history','user_id','users','id');

		$this->defineForeignKey('user_history_documents','document_id','documents','id');
		$this->defineForeignKey('user_history_documents','user_id','users','id');

		$this->defineForeignKey('user_history_folders','folder_id','folders','id');
		$this->defineForeignKey('user_history_folders','user_id','users','id');

		$this->defineForeignKey('users','authentication_source_id','authentication_sources','id');

		$this->defineForeignKey('users_groups_link', 'user_id','users','id');
		$this->defineForeignKey('users_groups_link', 'group_id','groups_lookup', 'id');

		$this->defineForeignKey('workflow_documents','document_id', 'documents','id');
		$this->defineForeignKey('workflow_documents','workflow_id', 'workflows','id');
		$this->defineForeignKey('workflow_documents','state_id','workflow_states','id');

		$this->defineForeignKey('workflow_state_actions','state_id','workflow_states','id');

		$this->defineForeignKey('workflow_state_disabled_actions','state_id','workflow_states','id');

		$this->defineForeignKey('workflow_state_permission_assignments','permission_id','permissions','id');
		$this->defineForeignKey('workflow_state_permission_assignments','permission_descriptor_id','permission_descriptors','id');
		$this->defineForeignKey('workflow_state_permission_assignments','workflow_state_id','workflow_states','id');

		$this->defineForeignKey('workflow_state_transitions','state_id','workflow_states','id');
		$this->defineForeignKey('workflow_state_transitions','transition_id','workflow_transitions','id');

		$this->defineForeignKey('workflow_states','workflow_id', 'workflows','id');
		$this->defineForeignKey('workflow_states','inform_descriptor_id', 'permission_descriptors','id');

		$this->defineForeignKey('workflow_transitions','workflow_id','workflows','id');
		$this->defineForeignKey('workflow_transitions','target_state_id','workflow_states','id');
		$this->defineForeignKey('workflow_transitions','guard_permission_id','permissions','id');
		$this->defineForeignKey('workflow_transitions','guard_condition_id','saved_searches','id');
		$this->defineForeignKey('workflow_transitions','guard_group_id','groups_lookup','id');
		$this->defineForeignKey('workflow_transitions','guard_role_id','roles','id');

		$this->defineForeignKey('workflow_trigger_instances','workflow_transition_id','workflow_transitions','id');

		$this->defineForeignKey('workflows','start_state_id','workflow_states','id');
	}

	/**
	 * Adds indexes that are not defined automatically via foreign key constraints and also adds criteria such as uniqueness.
	 *
	 */
	private function defineOtherIndexes()
	{
		$this->indexes = array();
		$this->defineIndex('active_sessions', 'session_id');
		$this->defineIndex('authentication_sources','namespace');

		$this->defineIndex('column_entries','view_namespace');

		$this->defineIndex('comment_searchable_text', 'body', 'FULLTEXT');


		$this->defineIndex('dashlet_disables','dashlet_namespace');
		$this->defineIndex('document_content_version','storage_path');

		$this->defineIndex('document_metadata_version','version_created');
		$this->defineIndex('document_role_allocations', array('document_id', 'role_id'));

		$this->defineIndex('document_searchable_text','document_text', 'FULLTEXT');

		$this->defineIndex('document_text','document_text', 'FULLTEXT');
		$this->defineIndex('document_transaction_text','document_text', 'FULLTEXT');

		$this->defineIndex('document_transaction_types_lookup','namespace', 'UNIQUE');

		$this->defineIndex('document_transactions','session_id');
		$this->defineIndex('document_transactions','user_id');
		$this->defineIndex('document_transactions','document_id');

		$this->defineIndex('document_types_lookup','name');

		$this->defineIndex('documents','created');
		$this->defineIndex('documents','modified');
		$this->defineIndex('documents','full_path','','(255)');
 		$this->defineIndex('documents','immutable');
		$this->defineIndex('documents','checkedout');

		$this->defineIndex('document_content_version','filename','','(255)');
		$this->defineIndex('document_content_version','size');

		$this->defineIndex('document_transactions',array('datetime','transaction_namespace'));

		$this->defineIndex('field_behaviour_options',array('behaviour_id','field_id'));

		$this->defineIndex('field_behaviours','name');

		$this->defineIndex('fieldsets','is_generic');
		$this->defineIndex('fieldsets','is_complete');
		$this->defineIndex('fieldsets','is_system');

		$this->defineIndex('field_orders','child_field_id', 'UNIQUE');

		$this->defineIndex('folder_searchable_text','folder_text' ,'FULLTEXT');

		$this->defineIndex('folder_transactions','folder_id');
		$this->defineIndex('folder_transactions','session_id');

		$this->defineIndex('folders', array('parent_id','name'));

		$this->defineIndex('groups_lookup','name', 'UNIQUE');
		$this->defineIndex('groups_lookup', array('authentication_source_id','authentication_details_s1'));

		$this->defineIndex('interceptor_instances','interceptor_namespace');

		$this->defineIndex('metadata_lookup','disabled');

		$this->defineIndex('metadata_lookup_tree','metadata_lookup_tree_parent');

		$this->defineIndex('mime_types','filetypes');
		$this->defineIndex('mime_types','mimetypes');

		$this->defineIndex('notifications','data_int_1');

		$this->defineIndex('organisations_lookup','name', 'UNIQUE');

		$this->defineIndex('permission_assignments', array('permission_object_id','permission_id'), 'UNIQUE');

		$this->defineIndex('permission_descriptor_groups','group_id');

		$this->defineIndex('permission_descriptor_roles','role_id');

		$this->defineIndex('permission_descriptor_users','user_id');

		$this->defineIndex('permission_descriptors','descriptor','UNIQUE');

		$this->defineIndex('permission_lookup_assignments', array('permission_lookup_id', 'permission_id'), 'UNIQUE');

		$this->defineIndex('permissions','name', 'UNIQUE');

		$this->defineIndex('plugins','namespace','UNIQUE');
		$this->defineIndex('plugins','disabled');

		$this->defineIndex('plugin_helper','namespace');
		$this->defineIndex('plugin_helper','plugin');
		$this->defineIndex('plugin_helper','classtype');


		$this->defineIndex('quicklinks','target_id');

		$this->defineIndex('roles','name','UNIQUE');
		$this->defineIndex('saved_searches','namespace','UNIQUE');

		$this->defineIndex('scheduler_tasks','task', 'UNIQUE');

		$this->defineIndex('system_settings','name', 'UNIQUE');

		$this->defineIndex('units_lookup','name' ,'UNIQUE');
		$this->defineIndex('units_lookup','folder_id' ,'UNIQUE');

		$this->defineIndex('upgrades','descriptor');
		$this->defineIndex('upgrades','parent');

		$this->defineIndex('user_history','action_namespace');
		$this->defineIndex('user_history','datetime');
		$this->defineIndex('user_history','session_id');

		$this->defineIndex('user_history_documents', array('user_id','document_id'));

		$this->defineIndex('user_history_folders', array('user_id','folder_id'));

		$this->defineIndex('users','username' ,'UNIQUE');
		$this->defineIndex('users','authentication_source_id');
		$this->defineIndex('users','last_login');
		$this->defineIndex('users','disabled');

		$this->defineIndex('workflow_states','name');
		$this->defineIndex('workflow_states','inform_descriptor_id'); //?

		$this->defineIndex('workflow_transitions',array('workflow_id','name'), 'UNIQUE');
		$this->defineIndex('workflow_transitions','name');
		$this->defineIndex('workflow_transitions','guard_permission_id'); //?

		$this->defineIndex('workflow_trigger_instances','namespace');

		$this->defineIndex('workflows','name', 'UNIQUE');
	}

	private function definePrimaryKey($table, $primaryKey)
	{
		$definition = new stdClass();
		$definition->table = $table;
		$definition->primaryKey = $primaryKey;
		$this->primaryKeys[] = $definition;
	}

	private function defineForeignKey($table, $field, $otherTable, $otherField, $onDelete='cascade', $onUpdate='cascade')
	{
		$definition = new stdClass();
		$definition->table = $table;
		$definition->field = $field;
		$definition->otherTable = $otherTable;
		$definition->otherField = $otherField;
		$definition->onDelete = $onDelete;
		$definition->onUpdate = $onUpdate;
		$this->foreignKeys[] = $definition;
	}

	private function defineIndex($table, $fields, $type='', $extra='')
	{
		$definition = new stdClass();
		$definition->table = $table;
		$definition->fields = $fields;
		$definition->type = $type;
		$definition->extra = $extra;
		$this->indexes[] = $definition;
	}

	public function createPrimaryKeys()
	{
		foreach($this->primaryKeys as $primaryKey)
		{
			$this->createPrimaryKey($primaryKey->table, $primaryKey->primaryKey);
		}
	}

	/**
	 * Add a primary key to a table.
	 *
	 * @param string $tablename
	 * @param string $primaryKey
	 */
	private function createPrimaryKey($tablename, $primaryKey)
	{
		if (!array_key_exists($tablename, $this->schema))
		{
			// if we don't know about the table, possibly it is in the commercial version.
			// exit gracefully.
			return;
		}

		if (is_array($primaryKey))
		{
			$primaryKey = implode(',', $primaryKey);
		}

		$sql="ALTER TABLE $tablename ADD PRIMARY KEY ($primaryKey)";
		$this->_exec($sql, false);

		if (strpos($primaryKey,',') === false)
		{
			// for some reason, there seems to be a problem periodically when adding foreign key constraints
			// unless there is a unique key. just a primary key isn't good enough for some reason. so for now,
			// we add the unique key, doubling up the effort of the primary key. we can drop these indexes again
			// later after the constraints have been added.
			$this->primary[$tablename] = $primaryKey;
			$sql="ALTER TABLE $tablename ADD UNIQUE KEY ($primaryKey)";
			$this->_exec($sql);
		}
	}

	public function createForeignKeys()
	{
		foreach($this->foreignKeys as $foreignKey)
		{
			$this->createForeignKey($foreignKey->table,$foreignKey->field,$foreignKey->otherTable,$foreignKey->otherField,$foreignKey->onDelete, $foreignKey->onUpdate);
		}
	}

	/**
	 * Add a foreign key constraint for a table.
	 *
	 * @param string $table
	 * @param string $field
	 * @param string $othertable
	 * @param string $otherfield
	 * @param string $ondelete
	 * @param string $onupdate
	 */
	private function createForeignKey($table, $field, $otherTable, $otherField, $onDelete='cascade', $onUpdate='cascade')
	{
		if (!array_key_exists($table, $this->schema) || !array_key_exists($otherTable, $this->schema))
		{
			// if we don't know about the tables, possibly it is in the commercial version.
			// exit gracefully.
			return;
		}

		$this->fixForeignKey($table, $field, $otherTable, $otherField);

		$sql = "ALTER TABLE $table ADD FOREIGN KEY ($field) REFERENCES $otherTable ($otherField) ";
		if ($onDelete != '')
		{
			$sql .= " ON DELETE $onDelete";
		}
		if ($onUpdate != '')
		{
			$sql .= " ON UPDATE $onUpdate";
		}
		$this->_exec($sql);
	}

	public function createIndexes()
	{
		foreach($this->indexes as $index)
		{
			$this->createIndex($index->table, $index->fields, $index->type, $index->extra);
		}
		$this->removeDuplicateIndexes();
	}

	private function fixForeignKey($table, $field, $otherTable, $otherField)
	{
		if ($table == $otherTable)
		{
			$this->_exec("create temporary table tmp_{$table}(id int);");
			$this->_exec("insert into tmp_{$table} select distinct id FROM {$table};");
			$this->_exec("insert into tmp_{$table} select distinct id FROM {$table};");
			$otherTable = "tmp_{$table}";
		}
		if ($otherTable == 'users' && $otherField == 'id')
		{
			$this->createFixUser();
			$sql = "UPDATE $table SET $field = -10 WHERE $field is not null and $field not in (select distinct id from users)";
			$this->_exec($sql);
			return;
		}

		$sql = "DELETE FROM $table WHERE $field is not null and $field not in (select distinct $otherField FROM $otherTable)";
		$this->_exec($sql);

		if ($table == $otherTable)
		{
			$this->_exec("drop table tmp_{$table};");
		}
	}

	/**
	 * Add an index to a table.
	 *
	 * @param string $table
	 * @param array $fields
	 * @param string $type
	 * @param string $extra
	 */
	private function createIndex($table, $fields, $type='', $extra='')
	{
		if (!array_key_exists($table, $this->schema))
		{
			// if we don't know about the tables, possibly it is in the commercial version.
			// exit gracefully.
			return;
		}

		if (!is_array($fields))
		{
			$fields = array($fields);
		}
		$index = implode('_', $fields);
		$fields = implode(',',$fields);
		$sql = "ALTER TABLE $table ADD $type INDEX $index ($fields$extra) ";
		$this->_exec($sql);
	}


	/**
	 * Drop all indexes and foreign key constraints from the system.
	 *
	 * @return int The number of elements cleared.
	 */
	private function getDBSchema()
	{
		$this->schema = array();
		$result = DBUtil::getResultArray('SHOW TABLES');
		$tables=array();

		foreach($result as $table)
		{
			$keys = array_keys($table);

			$tablename = $table[$keys[0]];
			if (substr($tablename,0,5) == 'zseq_')
			{
				continue;
			}

			$stmt = DBUtil::getResultArray("SHOW CREATE TABLE $tablename;");

			$keys = array_keys($stmt[0]);

			$sql = $stmt[0][$keys[1]];

			$this->schema[$tablename] = $sql;
		}
	}

	private function dropForeignKey($tablename, $foreignKey)
	{
		$sql = "ALTER TABLE $tablename DROP FOREIGN KEY $foreignKey;";
		return $this->_exec($sql);
	}

	/**
	 * Drops foreign keys based on the current schema
	 *
	 * @return int
	 */
	public function dropForeignKeys()
	{
		$dropped = 0;
		foreach($this->schema as $tablename=>$schema)
		{
			$lines = explode("\n", $schema);
			foreach($lines as $line)
			{
				if (strpos($line, 'CONSTRAINT') === false)
				{
					continue;
				}
				preg_match_all('(\`([^\`])*\`)',$line, $params);

				$constraint=substr($params[0][0],1,-1);
				$table= substr($params[0][2],1,-1);

				($this->dropForeignKey($tablename, $constraint));
				$dropped++;
			}
		}
		return $dropped;
	}

	/**
	 * Drops primary keys based on the current schema
	 *
	 * @return int
	 */
	public function dropPrimaryKeys()
	{
		$dropped = 0;
		foreach($this->schema as $tablename=>$schema)
		{
			$lines = explode("\n", $schema);
			foreach($lines as $line)
			{
				if (strpos($line, 'PRIMARY KEY') === false)
				{
					continue;
				}

				($this->dropPrimaryKey($tablename));
				$dropped++;
			}
		}
		return $dropped;
	}

	/**
	 * Drops the primary key from a table
	 *
	 * @param string $tablename
	 */
	private function dropPrimaryKey($tablename)
	{
		$sql = "ALTER TABLE $tablename DROP primary key;";
		return $this->_exec($sql,false);
	}

	/**
	 * Drops indexes based on the current schema
	 *
	 * @return int
	 */
	public function dropIndexes()
	{
		$dropped = 0;
		foreach($this->schema as $tablename=>$schema)
		{
			$lines = explode("\n", $schema);
			foreach($lines as $line)
			{
				if (strpos($line, 'KEY') === false)
				{
					continue;
				}

				if (strpos($line, 'PRIMARY KEY') !== false)
				{
					continue;
				}

				if (strpos($line, 'FOREIGN KEY') !== false)
				{
					continue;
				}

				preg_match_all('(\`([^\`])*\`)',$line, $params);

				$key = substr($params[0][0],1,-1);
				($this->dropIndex($tablename, $key));
				$dropped++;
			}
		}
		return $dropped;
	}


	/**
	 * Drop an index from the database.
	 *
	 * @param string $table
	 * @param string $field
	 */
	function dropIndex($table, $field)
	{
		if (!is_array($fields)) $field = array($field);
		$field = implode('_', $field);
		$sql = "ALTER TABLE $table DROP INDEX $field";
		$result = $this->_exec($sql);

		if (!$result)
		{
			//print "...";
		}

		return $result;
	}

	/**
	 * Execute a db sql statement on the database.
	 *
	 * @param string $sql
	 * @return boolean
	 */
	private function _exec($sql )
	{
		global $default;
		if (!$this->persist)
		{
			print "$sql\n";
			return;
		}
		$this->log("Action: $sql");
		$rs = DBUtil::runQuery($sql, $default->_admindb );
		if (PEAR::isError($rs))
		{
			$this->log("* " . $rs->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Logs a message to the log file
	 *
	 * @param string $msg
	 */
	private function log($msg, $level='info')
	{
		global $default;
		$default->log->$level('KTSchemaUtil: ' .$msg);
	}
}


?>
