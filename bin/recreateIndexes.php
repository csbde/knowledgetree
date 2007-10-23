<?php

/**
 * $Id:$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
 *
 */

/*
 * PURPOSE: This script will recreate the indexes on the database. It will also attempt to add foreign key constraints.
 *
 * NOTE: It was developed on mysql 5, so there may be a requirement for this to be running!
 * NOTE: This assumes that the db is in the 3.5.0 state!
 *
 * It will produce 'errors' when there are issues. Many may be ignored as some do not apply to open source.
 */

require_once('../config/dmsDefaults.php');

print _kt('Recreate DB Indexes') . "...\n\n";

$recreator = new IndexRecreator();
$recreator->globalStart();

do
{
	$dropped = $recreator->dropIndexes();
} while ($dropped != 0);

$recreator->applyPreFixes();
$recreator->addPrimaryKeys();
$recreator->addForeignKeys();
$recreator->removeDuplicateIndexes();
$recreator->addOtherIndexes();
$recreator->applyPostFixes();

print sprintf(_kt('Total time: %s'), $recreator->globalEnd());

print _kt('Done.') . "\n";
exit;

class IndexRecreator
{
	var $knownKeys;
	var $knownPrimary;
	var $newPrimary;
	var $newKeys;
	var $exec;
	var $debugSQL = false;
	var $verbose = true;

	var $foreignkeys;
	var $primary;
	var $globalstart;
	var $start;
	var $tables;

	function microtimeFloat()
	{
    	list($usec, $sec) = explode(" ", microtime());
    	return ((float)$usec + (float)$sec);
	}

	function globalStart()
	{
		$this->globalstart = $this->microtimeFloat();
	}

	function start()
	{
		$this->start = $this->microtimeFloat();
	}

	function globalEnd()
	{
		$time = $this->microtimeFloat() - $this->globalstart;

		return number_format($time,2,'.',',') . 's';
	}

	function end()
	{
		$time = $this->microtimeFloat() - $this->start;
		return number_format($time,2,'.',',') . "s";
	}

	function IndexRecreator()
	{
		$this->knownKeys = array();
		$this->knownPrimary = array();
		$this->newPrimary = array();
		$this->newKeys = array();
		$this->exec = true;
	}

	function applyPreFixes()
	{

	}

	function addForeignKeys()
	{
		$this->addForeignKey('active_sessions', 'user_id', 'users', 'id');

		$this->addForeignKey('archive_restoration_request', 'document_id', 'documents', 'id');
		$this->addForeignKey('archive_restoration_request', 'request_user_id', 'users', 'id');
		$this->addForeignKey('archive_restoration_request', 'admin_user_id', 'users', 'id');

		$this->addForeignKey('archiving_settings', 'archiving_type_id', 'archiving_type_lookup', 'id');
		$this->addForeignKey('archiving_settings', 'time_period_id', 'time_period', 'id');

		$this->addForeignKey('baobab_user_keys', 'user_id', 'users', 'id');
		$this->addForeignKey('baobab_user_keys', 'key_id', 'baobab_keys', 'id');

		$this->addForeignKey('comment_searchable_text', 'comment_id', 'discussion_comments', 'id');
		$this->addForeignKey('comment_searchable_text', 'document_id', 'documents', 'id');

		$this->addForeignKey('dashlet_disables', 'user_id', 'users', 'id');

		$this->addForeignKey('discussion_comments', 'thread_id', 'discussion_threads', 'id');
		$this->addForeignKey('discussion_comments', 'user_id', 'users', 'id');
		$this->addForeignKey('discussion_comments', 'in_reply_to', 'discussion_comments', 'id');

		$this->addForeignKey('discussion_threads', 'document_id', 'documents', 'id');
		$this->addForeignKey('discussion_threads', 'first_comment_id', 'discussion_comments', 'id');
		$this->addForeignKey('discussion_threads', 'last_comment_id', 'discussion_comments', 'id');
		$this->addForeignKey('discussion_threads', 'creator_id', 'users', 'id');

		$this->addForeignKey('document_archiving_link', 'document_id', 'documents', 'id');
		$this->addForeignKey('document_archiving_link', 'archiving_settings_id', 'archiving_settings', 'id');

		$this->addForeignKey('document_content_version', 'document_id', 'documents', 'id');
		$this->addForeignKey('document_content_version', 'mime_id', 'mime_types', 'id');

		$this->addForeignKey('document_fields','parent_fieldset','fieldsets','id');

		$this->addForeignKey('document_fields_link','document_field_id','document_fields','id');
		$this->addForeignKey('document_fields_link','metadata_version_id','document_metadata_version','id');

		$this->addForeignKey('document_link','parent_document_id', 'documents', 'id');
		$this->addForeignKey('document_link','child_document_id', 'documents', 'id');
		$this->addForeignKey('document_link','link_type_id','document_link_types','id');

		$this->addForeignKey('document_metadata_version','document_type_id','document_types_lookup','id');
		$this->addForeignKey('document_metadata_version','status_id','status_lookup','id');
		$this->addForeignKey('document_metadata_version','document_id','documents','id');
		$this->addForeignKey('document_metadata_version','version_creator_id','users','id');
		$this->addForeignKey('document_metadata_version','content_version_id','document_content_version','id');
		$this->addForeignKey('document_metadata_version','workflow_id','workflows','id');
		$this->addForeignKey('document_metadata_version','workflow_state_id','workflow_states','id');

		$this->addForeignKey('document_role_allocations','role_id','roles','id');
		$this->addForeignKey('document_role_allocations','permission_descriptor_id','permission_descriptors','id');

		$this->addForeignKey('document_searchable_text','document_id','documents','id');

		$this->addForeignKey('document_subscriptions','user_id','users','id');
		$this->addForeignKey('document_subscriptions','document_id','documents','id');

		$this->addForeignKey('document_tags','document_id','documents','id');
		$this->addForeignKey('document_tags','tag_id','tag_words','id');

		$this->addForeignKey('document_text','document_id','documents','id');


		$this->addForeignKey('document_transaction_text','document_id','documents','id');

		$this->addForeignKey('document_type_fields_link','document_type_id', 'document_types_lookup','id');
		$this->addForeignKey('document_type_fields_link','field_id','document_fields','id');

		$this->addForeignKey('document_type_fieldsets_link','document_type_id', 'document_types_lookup','id');
		$this->addForeignKey('document_type_fieldsets_link','fieldset_id','fieldsets','id');

		$this->addForeignKey('documents','creator_id','users','id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('documents','folder_id','folders','id'); // we don't want this
		$this->addForeignKey('documents','checked_out_user_id','users','id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('documents','status_id','status_lookup','id');
		$this->addForeignKey('documents','permission_object_id','permission_objects','id');
		$this->addForeignKey('documents','permission_lookup_id','permission_lookups','id');
		$this->addForeignKey('documents','modified_user_id','users','id', 'SET NULL', 'SET NULL');
		$this->addForeignKey('documents','metadata_version_id','document_metadata_version','id');

		$this->addForeignKey('download_files','document_id','documents','id');

		$this->addForeignKey('field_behaviour_options','behaviour_id','field_behaviours','id');
		$this->addForeignKey('field_behaviour_options','field_id','document_fields','id');
		$this->addForeignKey('field_behaviour_options','instance_id','field_value_instances','id');

		$this->addForeignKey('field_behaviours','field_id','document_fields','id');

		$this->addForeignKey('field_orders','child_field_id','document_fields','id');
		$this->addForeignKey('field_orders','parent_field_id','document_fields','id');
		$this->addForeignKey('field_orders','fieldset_id','fieldsets','id');

		$this->addForeignKey('field_value_instances','field_value_id','metadata_lookup','id'); // it is so.. strange ;)
		$this->addForeignKey('field_value_instances','behaviour_id','field_behaviours','id');
		$this->addForeignKey('field_value_instances','field_id','document_fields','id');

		$this->addForeignKey('fieldsets','master_field','document_fields','id');

		$this->addForeignKey('folder_descendants','parent_id','folders','id');
		$this->addForeignKey('folder_descendants','folder_id','folders','id');

		$this->addForeignKey('folder_doctypes_link','folder_id','folders','id');
		$this->addForeignKey('folder_doctypes_link','document_type_id','document_types_lookup','id');

		$this->addForeignKey('folder_searchable_text','folder_id','folders','id');

		$this->addForeignKey('folder_subscriptions','user_id','users','id');
		$this->addForeignKey('folder_subscriptions','folder_id','folders','id');

		$this->addForeignKey('folder_workflow_map','folder_id', 'folders','id');
		$this->addForeignKey('folder_workflow_map','workflow_id', 'workflows','id');

		$this->addForeignKey('folders','creator_id','users','id');
		$this->addForeignKey('folders','permission_object_id','permission_objects','id');
		$this->addForeignKey('folders','permission_lookup_id','permission_lookups','id');
		$this->addForeignKey('folders','parent_id','folders','id');

		$this->addForeignKey('folders_users_roles_link','user_id','users','id');
		$this->addForeignKey('folders_users_roles_link','document_id','documents','id');

		$this->addForeignKey('groups_groups_link','parent_group_id','groups_lookup','id');
		$this->addForeignKey('groups_groups_link','member_group_id','groups_lookup','id');

		$this->addForeignKey('groups_lookup','unit_id', 'units_lookup','id');

		$this->addForeignKey('index_files','document_id','documents','id');
		$this->addForeignKey('index_files','user_id','users','id');

		$this->addForeignKey('metadata_lookup','document_field_id','document_fields','id');
//		$this->addForeignKey('metadata_lookup','treeorg_parent','??','id');

		$this->addForeignKey('metadata_lookup_tree','document_field_id', 'document_fields','id');
//		$this->addForeignKey('metadata_lookup_tree','metadata_lookup_tree_parent', '??','id');

		$this->addForeignKey('mime_types','mime_document_id','mime_documents','id', 'set null', 'set null');
		$this->addForeignKey('mime_types','extractor_id','mime_extractors','id', 'set null', 'set null');

		$this->addForeignKey('mime_document_mapping','mime_type_id','mime_types','id');
		$this->addForeignKey('mime_document_mapping','mime_document_id','mime_documents','id');

		$this->addForeignKey('news','image_mime_type_id','mime_types','id');

		$this->addForeignKey('notifications','user_id', 'users','id');

		$this->addForeignKey('permission_assignments','permission_id', 'permissions','id');
		$this->addForeignKey('permission_assignments','permission_object_id','permission_objects','id'); // duplicate
		$this->addForeignKey('permission_assignments','permission_descriptor_id','permission_descriptors','id');

		$this->addForeignKey('permission_descriptor_groups','descriptor_id','permission_descriptors','id');
		$this->addForeignKey('permission_descriptor_groups','group_id','groups_lookup','id');

		$this->addForeignKey('permission_descriptor_roles','descriptor_id','permission_descriptors','id');
		$this->addForeignKey('permission_descriptor_roles','role_id','roles','id');

		$this->addForeignKey('permission_descriptor_users','descriptor_id','permission_descriptors','id');
		$this->addForeignKey('permission_descriptor_users','user_id','users','id');

		$this->addForeignKey('permission_dynamic_assignments','dynamic_condition_id','permission_dynamic_conditions','id');
		$this->addForeignKey('permission_dynamic_assignments','permission_id','permissions','id');

		$this->addForeignKey('permission_dynamic_conditions','permission_object_id','permission_objects','id');
		$this->addForeignKey('permission_dynamic_conditions','group_id','groups_lookup','id');
		$this->addForeignKey('permission_dynamic_conditions','condition_id','saved_searches','id');

		$this->addForeignKey('permission_lookup_assignments','permission_id','permissions','id');
		$this->addForeignKey('permission_lookup_assignments','permission_lookup_id','permission_lookups','id'); // duplicate
		$this->addForeignKey('permission_lookup_assignments','permission_descriptor_id','permission_descriptors','id');

		$this->addForeignKey('plugin_rss','user_id','users','id');

		$this->addForeignKey('quicklinks','user_id','users','id');

		$this->addForeignKey('role_allocations','folder_id','folders','id');
		$this->addForeignKey('role_allocations','role_id', 'roles','id');
		$this->addForeignKey('role_allocations','permission_descriptor_id','permission_descriptors','id');

		$this->addForeignKey('saved_searches','user_id','users','id');

		$this->addForeignKey('search_document_user_link','document_id','documents','id');
		$this->addForeignKey('search_document_user_link','user_id','users','id');

	 	$this->addForeignKey('search_saved','user_id','users','id');
	 	$this->addForeignKey('search_saved_events','document_id','documents','id');

	 	$this->addForeignKey('time_period','time_unit_id','time_unit_lookup','id');

	 	$this->addForeignKey('type_workflow_map','document_type_id','document_types_lookup','id');
	 	$this->addForeignKey('type_workflow_map','workflow_id','workflows','id');

		$this->addForeignKey('units_lookup','folder_id','folders','id');

		$this->addForeignKey('units_organisations_link','unit_id','units_lookup','id');
		$this->addForeignKey('units_organisations_link','organisation_id','organisations_lookup','id');

		$this->addForeignKey('uploaded_files','userid','users','id');
		$this->addForeignKey('uploaded_files','document_id','documents','id');

		$this->addForeignKey('user_history','user_id','users','id');

		$this->addForeignKey('user_history_documents','document_id','documents','id');
		$this->addForeignKey('user_history_documents','user_id','users','id');

		$this->addForeignKey('user_history_folders','folder_id','folders','id');
		$this->addForeignKey('user_history_folders','user_id','users','id');

		$this->addForeignKey('users','authentication_source_id','authentication_sources','id');

		$this->addForeignKey('users_groups_link', 'user_id','users','id');
		$this->addForeignKey('users_groups_link', 'group_id','groups_lookup', 'id');

		$this->addForeignKey('workflow_documents','document_id', 'documents','id');
		$this->addForeignKey('workflow_documents','workflow_id', 'workflows','id');
		$this->addForeignKey('workflow_documents','state_id','workflow_states','id');

		$this->addForeignKey('workflow_state_actions','state_id','workflow_states','id');

		$this->addForeignKey('workflow_state_disabled_actions','state_id','workflow_states','id');

		$this->addForeignKey('workflow_state_permission_assignments','permission_id','permissions','id');
		$this->addForeignKey('workflow_state_permission_assignments','permission_descriptor_id','permission_descriptors','id');
		$this->addForeignKey('workflow_state_permission_assignments','workflow_state_id','workflow_states','id');

		$this->addForeignKey('workflow_state_transitions','state_id','workflow_states','id');
		$this->addForeignKey('workflow_state_transitions','transition_id','workflow_transitions','id');

		$this->addForeignKey('workflow_states','workflow_id', 'workflows','id');
		$this->addForeignKey('workflow_states','inform_descriptor_id', 'permission_descriptors','id');

		$this->addForeignKey('workflow_transitions','workflow_id','workflows','id');
		$this->addForeignKey('workflow_transitions','target_state_id','workflow_states','id');
		$this->addForeignKey('workflow_transitions','guard_permission_id','permissions','id');
		$this->addForeignKey('workflow_transitions','guard_condition_id','saved_searches','id');
		$this->addForeignKey('workflow_transitions','guard_group_id','groups_lookup','id');
		$this->addForeignKey('workflow_transitions','guard_role_id','roles','id');

		$this->addForeignKey('workflow_trigger_instances','workflow_transition_id','workflow_transitions','id');

		$this->addForeignKey('workflows','start_state_id','workflow_states','id');

	}

	function removeDuplicateIndexes()
	{
		foreach($this->primary as $table=>$key)
		{
			$this->dropIndex($table,$key);
		}

	}
	function addOtherIndexes()
	{
		$this->addIndex('active_sessions', 'session_id');
		$this->addIndex('authentication_sources','namespace');

		$this->addIndex('column_entries','view_namespace');

		$this->addIndex('comment_searchable_text', 'body', 'FULLTEXT');


		$this->addIndex('dashlet_disables','dashlet_namespace');
		$this->addIndex('document_content_version','storage_path');

		$this->addIndex('document_metadata_version','version_created');
		$this->addIndex('document_role_allocations', array('document_id', 'role_id'));

		$this->addIndex('document_searchable_text','document_text', 'FULLTEXT');

		$this->addIndex('document_text','document_text', 'FULLTEXT');
		$this->addIndex('document_transaction_text','document_text', 'FULLTEXT');

		$this->addIndex('document_transaction_types_lookup','namespace', 'UNIQUE');

		$this->addIndex('document_transactions','session_id');
		$this->addIndex('document_transactions','document_id');

		$this->addIndex('document_types_lookup','name');
		//$this->addIndex('document_types_lookup','disabled'); ? used

		$this->addIndex('documents','created');
		$this->addIndex('documents','modified');
		$this->addIndex('documents','full_path','','(255)');
 		$this->addIndex('documents','immutable');
		$this->addIndex('documents','checkedout');

		$this->addIndex('document_content_version','filename','','(255)');
		$this->addIndex('document_content_version','size');

		$this->addIndex('field_behaviour_options',array('behaviour_id','field_id'));

		$this->addIndex('field_behaviours','name');

		$this->addIndex('fieldsets','is_generic');
		$this->addIndex('fieldsets','is_complete');
		$this->addIndex('fieldsets','is_system');

		$this->addIndex('field_orders','child_field_id', 'UNIQUE');

		$this->addIndex('folder_searchable_text','folder_text' ,'FULLTEXT');

		$this->addIndex('folder_transactions','folder_id');
		$this->addIndex('folder_transactions','session_id');

//		$this->addIndex('folders','name');
		$this->addIndex('folders', array('parent_id','name'));

		$this->addIndex('groups_lookup','name', 'UNIQUE');
		$this->addIndex('groups_lookup', array('authentication_source_id','authentication_details_s1'));

		$this->addIndex('interceptor_instances','interceptor_namespace'); // unique?

		$this->addIndex('metadata_lookup','disabled');
		//$this->addNewIndex('metadata_lookup','is_stuck'); don't think this is used anywhere....

		$this->addIndex('metadata_lookup_tree','metadata_lookup_tree_parent');

		$this->addIndex('mime_types','filetypes');
		$this->addIndex('mime_types','mimetypes');

		$this->addIndex('notifications','data_int_1'); // document id seems to be stored in this. used by clearnotifications.
//		$this->addIndex('notifications','type'); // don't think this is used

		$this->addIndex('organisations_lookup','name', 'UNIQUE');

		$this->addIndex('permission_assignments', array('permission_object_id','permission_id'), 'UNIQUE'); // note change of order
//		$this->dropIndex('permission_assignments','permission_object_id'); // duplicate

		//$this->dropIndex('permission_descriptor_groups','descriptor_id'); // in primary key
		$this->addIndex('permission_descriptor_groups','group_id');

		//$this->dropIndex('permission_descriptor_roles','descriptor_id'); // in primary key
		$this->addIndex('permission_descriptor_roles','role_id');

		//$this->dropIndex('permission_descriptor_users','descriptor_id'); // in primary
		$this->addIndex('permission_descriptor_users','user_id');

		$this->addIndex('permission_descriptors','descriptor','UNIQUE');

		$this->addIndex('permission_lookup_assignments', array('permission_lookup_id', 'permission_id'), 'UNIQUE');
		//$this->dropIndex('permission_lookup_assignments','permission_lookup_id'); // in composite

		$this->addIndex('permissions','name', 'UNIQUE');

		$this->addIndex('plugins','namespace','UNIQUE');
		$this->addIndex('plugins','disabled');

		$this->addIndex('quicklinks','target_id');

		$this->addIndex('roles','name','UNIQUE');
		$this->addIndex('saved_searches','namespace','UNIQUE');

		$this->addIndex('system_settings','name', 'UNIQUE');

		$this->addIndex('units_lookup','name' ,'UNIQUE');
//		$this->dropIndex('units_lookup','folder_id');
		$this->addIndex('units_lookup','folder_id' ,'UNIQUE');

		$this->addIndex('upgrades','descriptor');
		$this->addIndex('upgrades','parent');

		$this->addIndex('user_history','action_namespace');
		$this->addIndex('user_history','datetime');
		$this->addIndex('user_history','session_id');

		$this->addIndex('user_history_documents', array('user_id','document_id'));
		//$this->dropIndex('user_history_documents', 'user_id'); // duplicate

		$this->addIndex('user_history_folders', array('user_id','folder_id'));
		//$this->dropIndex('user_history_folders', 'user_id'); // duplicate

		$this->addIndex('users','username' ,'UNIQUE');
		$this->addIndex('users','authentication_source_id');
		//$this->addNewIndex('users','authentication_details_b1');
		//$this->addNewIndex('users','authentication_details_b2');
		$this->addIndex('users','last_login');
		$this->addIndex('users','disabled');

		$this->addIndex('workflow_states','name');
		$this->addIndex('workflow_states','inform_descriptor_id'); //?

		$this->addIndex('workflow_transitions',array('workflow_id','name'), 'UNIQUE');
		//$this->dropIndex('workflow_transitions','workflow_id'); // duplicate
		$this->addIndex('workflow_transitions','name');
		$this->addIndex('workflow_transitions','guard_permission_id'); //?

		$this->addIndex('workflow_trigger_instances','namespace');

		$this->addIndex('workflows','name', 'UNIQUE');


	}

	function applyPostFixes()
	{

	}

	function dropIndex($table, $field)
	{
		if (!is_array($fields)) $field = array($field);
		$field = implode('_', $field);
		$sql = "alter table $table drop index $field";
		$this->_exec($sql);
	}

	function addIndex($table, $fields, $type='', $extra='')
	{
		if (!in_array($table, $this->tables)) return;

		if (!is_array($fields)) $fields = array($fields);
		$index = implode('_', $fields);
		//$index = str_replace('_id','',$index);
		$fields = implode(',',$fields);
		$sql = "alter table $table add $type index $index ($fields$extra) ";
		$this->_exec($sql);
	}

	function addForeignKey($table, $field, $othertable, $otherfield, $ondelete='cascade', $onupdate='cascade')
	{
		if (!in_array($table, $this->tables)) return;
		if (!in_array($othertable, $this->tables)) return;

		$sql = "alter table $table add foreign key ($field) references $othertable ($otherfield) ";
		if ($ondelete != '')
			$sql .= " on delete $ondelete";
		if ($onupdate != '')
			$sql .= " on update $onupdate";
		$this->_exec($sql);
	}

	function addPrimaryKeys()
	{
		$this->addPrimaryKey('active_sessions', 'id');
		$this->addPrimaryKey('archive_restoration_request','id');
		$this->addPrimaryKey('archiving_settings','id');
		$this->addPrimaryKey('archiving_type_lookup','id');
		$this->addPrimaryKey('authentication_sources','id');
		$this->addPrimaryKey('baobab_keys','id');
		$this->addPrimaryKey('baobab_user_keys','id');
		$this->addPrimaryKey('column_entries','id');
		$this->addPrimaryKey('comment_searchable_text','comment_id');
		$this->addPrimaryKey('dashlet_disables','id');
		$this->addPrimaryKey('data_types','id');
		$this->addPrimaryKey('discussion_comments','id');
		$this->addPrimaryKey('discussion_threads','id');
		$this->addPrimaryKey('document_archiving_link','id');
		$this->addPrimaryKey('document_content_version','id');
		$this->addPrimaryKey('document_fields','id');
		$this->addPrimaryKey('document_fields_link','id');
		$this->addPrimaryKey('document_incomplete','id');
		$this->addPrimaryKey('document_link','id');
		$this->addPrimaryKey('document_link_types','id');
		$this->addPrimaryKey('document_metadata_version','id');
		$this->addPrimaryKey('document_role_allocations','id');
		$this->addPrimaryKey('document_subscriptions','id');
		$this->addPrimaryKey('document_tags',array('document_id','tag_id'));
		$this->addPrimaryKey('document_text', 'document_id');
		$this->addPrimaryKey('document_transaction_types_lookup', 'id');
		$this->addPrimaryKey('document_transaction_text', 'document_id');
		$this->addPrimaryKey('document_transactions','id');
		$this->addPrimaryKey('document_type_fields_link','id');
		$this->addPrimaryKey('document_type_fieldsets_link','id');
		$this->addPrimaryKey('document_types_lookup','id');
		$this->addPrimaryKey('documents','id');
		$this->addPrimaryKey('download_files',array('document_id','session'));
		$this->addPrimaryKey('field_behaviours','id');
		$this->addPrimaryKey('field_value_instances','id');
		$this->addPrimaryKey('fieldsets','id');
		$this->addPrimaryKey('folder_doctypes_link','id');
		$this->addPrimaryKey('folder_searchable_text','folder_id');
		$this->addPrimaryKey('folder_subscriptions','id');
		$this->addPrimaryKey('folder_transactions','id');
		$this->addPrimaryKey('folder_workflow_map','folder_id');
		$this->addPrimaryKey('folders','id');
		$this->addPrimaryKey('folders_users_roles_link','id');
		$this->addPrimaryKey('groups_groups_link','id');
		$this->addPrimaryKey('groups_lookup','id');
		$this->addPrimaryKey('help','id');
		$this->addPrimaryKey('help_replacement','id');
		$this->addPrimaryKey('index_files','document_id');
		$this->addPrimaryKey('interceptor_instances','id');
		$this->addPrimaryKey('links','id');
		$this->addPrimaryKey('metadata_lookup','id');
		$this->addPrimaryKey('metadata_lookup_tree','id');
		$this->addPrimaryKey('mime_documents','id');
		$this->addPrimaryKey('mime_extractors','id');
		$this->addPrimaryKey('mime_document_mapping',array('mime_type_id','mime_document_id'));
		$this->addPrimaryKey('mime_types','id');
		$this->addPrimaryKey('news','id');
		$this->addPrimaryKey('notifications','id');
		$this->addPrimaryKey('organisations_lookup','id');
		$this->addPrimaryKey('permission_assignments','id');
		$this->addPrimaryKey('permission_descriptor_groups', array('descriptor_id','group_id'));
		$this->addPrimaryKey('permission_descriptor_roles', array('descriptor_id','role_id'));
		$this->addPrimaryKey('permission_descriptor_users', array('descriptor_id','user_id'));
		$this->addPrimaryKey('permission_descriptors','id');
		$this->addPrimaryKey('permission_dynamic_conditions','id');
		$this->addPrimaryKey('permission_lookup_assignments','id');
		$this->addPrimaryKey('permission_lookups','id');
		$this->addPrimaryKey('permission_objects','id');
		$this->addPrimaryKey('permissions','id');
		$this->addPrimaryKey('plugin_rss','id');
		$this->addPrimaryKey('plugins','id');
		$this->addPrimaryKey('quicklinks','id');
		$this->addPrimaryKey('role_allocations','id');
		$this->addPrimaryKey('roles','id');
		$this->addPrimaryKey('saved_searches','id');
		$this->addPrimaryKey('scheduler_tasks','id');
		$this->addPrimaryKey('search_ranking',array('groupname','itemname'));
		$this->addPrimaryKey('search_saved','id');
		$this->addPrimaryKey('search_saved_events','document_id');
		$this->addPrimaryKey('status_lookup','id');
		$this->addPrimaryKey('system_settings','id');
		$this->addPrimaryKey('tag_words','id');
		$this->addPrimaryKey('time_period','id');
		$this->addPrimaryKey('time_unit_lookup','id');
		$this->addPrimaryKey('trigger_selection','event_ns');
		$this->addPrimaryKey('type_workflow_map','document_type_id');
		$this->addPrimaryKey('units_lookup','id');
		$this->addPrimaryKey('units_organisations_link','id');
		$this->addPrimaryKey('upgrades','id');
		$this->addPrimaryKey('uploaded_files','tempfilename');
		$this->addPrimaryKey('user_history','id');
		$this->addPrimaryKey('user_history_documents','id');
		$this->addPrimaryKey('user_history_folders','id');
		$this->addPrimaryKey('users','id');
		$this->addPrimaryKey('users_groups_link','id');
		$this->addPrimaryKey('workflow_actions','workflow_id');
		$this->addPrimaryKey('workflow_documents','document_id');
		$this->addPrimaryKey('workflow_state_permission_assignments','id');
		$this->addPrimaryKey('workflow_states','id');
		$this->addPrimaryKey('workflow_state_transitions',array('state_id','transition_id'));
		$this->addPrimaryKey('workflow_transitions','id');
		$this->addPrimaryKey('workflow_trigger_instances','id');
		$this->addPrimaryKey('workflows','id');
	}

	function addPrimaryKey($table, $primarykey)
	{
		if (!in_array($table, $this->tables)) return;
		if (is_array($primarykey))
		{
			$primarykey = implode(',', $primarykey);
		}

		$sql="alter table $table add primary key ($primarykey)";
		$this->_exec($sql, false);

		if (strpos($primarykey,',') === false)
		{
			$this->primary[$table] = $primarykey;
			$sql="alter table $table add unique key ($primarykey)";
			$this->_exec($sql);
		}
	}

	function dropIndexes()
	{
		$result = DBUtil::getResultArray("show tables");
		$tables=array();
		$this->tables = array();

		foreach($result as $table)
		{
			$keys = array_keys($table);

			$tablename = $table[$keys[0]];
			if (substr($tablename,0,5) == 'zseq_')
			{
				continue;
			}

			$stmt = DBUtil::getResultArray("show create table $tablename");
			$this->tables[] = $tablename;

			$keys = array_keys($stmt[0]);

			$sql = $stmt[0][$keys[1]];

			$table = array('fks'=>array(), 'pk'=>array(), 'keys'=>array());
			$lines = explode("\n", $sql);
			foreach($lines as $line)
			{
				$line = trim($line);
				if (strpos($line, 'PRIMARY KEY') === 0)
				{
					preg_match('(\`([^\`])*\`)',$line, $params);
					$primaryKey = explode(',', $params[0]);
					foreach($primaryKey as $value)
					{
						$fieldname = substr($value,1,-1);
						$table['pk'][] = $fieldname;
					}
					continue;
				}
				elseif (strpos($line, 'CONSTRAINT') === 0)
				{
					preg_match_all('(\`([^\`])*\`)',$line, $params);

					$fieldname = substr($params[0][1],1,-1);

					$table['fks'][$fieldname] = array(
						'constraint'=>substr($params[0][0],1,-1),
						'table'=>substr($params[0][2],1,-1),
						'field'=>substr($params[0][3],1,-1)
					);
					continue;
				}
				elseif (strpos($line, 'KEY') !== false)
				{
					preg_match_all('(\`([^\`])*\`)',$line, $params);
					$fieldname = substr($params[0][1],1,-1);
					$key = substr($params[0][0],1,-1);
					$table['keys'][$fieldname] = array('name'=>$key, 'unique'=>false);
					if (strpos($line, 'UNIQUE KEY') !== false)
					{
						if (count($params[0]) == 2)
						{
							$table['keys'][$fieldname]['unique']=true;
						}
					}
					continue;
				}
			}

			$tables[$tablename]= $table;
		}

		$dropped = 0;

		// drop foreign keys
		foreach($tables as $tablename=>$table)
		{
			foreach($table['fks'] as $fieldname=>$constraint)
			{
				$name = $constraint['constraint'];
				$table = $constraint['table'];
				$field = $constraint['field'];
				$sql = "ALTER TABLE $tablename DROP FOREIGN KEY $name;";
				if ($this->_exec($sql)) $dropped++;
			}
		}

		// drop primary keys
		foreach($tables as $tablename=>$table)
		{
			foreach($table['pk'] as $fieldname)
			{
				$sql = "ALTER TABLE $tablename DROP primary key;";
				if ($this->_exec($sql,false)) $dropped++;
				break;
			}
		}

		// drop normal indexes

		foreach($tables as $tablename=>$table)
		{
			foreach($table['keys'] as $fieldname=>$keyinfo)
			{
				$keyname = $keyinfo['name'];
				$sql = "ALTER TABLE $tablename DROP key $keyname;";
				if ($this->_exec($sql)) $dropped++;
				break;
			}
		}


		return $dropped;
	}

	function _exec($sql, $report = true)
	{
		print "Action: $sql";
		$this->start();
		$rs = DBUtil::runQuery($sql);
		print " - " . $this->end() . "\n";
		if (PEAR::isError($rs))
		{
			if ($report) print "* " . $rs->getMessage() . "\n";
			return false;
		}
		return true;
	}

}


?>