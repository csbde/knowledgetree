<?
/**
 * This is the basic configuration for the KnowledgeTree WebService object model 
 */

/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

// Path to PEAR libraries

define('KT_PEAR_DIR',					'c:/kt/kt.trunk/thirdparty/pear');

// URL to KnowledgeTree installation
// This must be configured by the administrator installing this component.

define('KTWebServiceURL',				'http://ktdms.trunk');

define('KTWebService_WSDL',				KTWebServiceURL . '/ktwebservice/?wsdl');
define('KTUploadURL',					KTWebServiceURL . '/ktwebservice/upload.php');

?>