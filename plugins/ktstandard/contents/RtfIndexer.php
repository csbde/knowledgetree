<?php

/**
 * $Id$
 *
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

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTRtfIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'text/rtf' => true,
    );
    var $command = 'catdoc';          // could be any application.
    var $commandconfig = 'indexer/catdoc';          // could be any application.
    var $args = array("-w", "-d", "UTF-8");
    var $use_pipes = true;
    
    function findLocalCommand() {
        $sCommand = KTUtil::findCommand($this->commandconfig, $this->command);
	putenv('LANG=en_US.UTF-8');
        return $sCommand;
    }
    
    function getDiagnostic() {
        if (OS_WINDOWS) {
            return null; // _kt("The RTF indexer does not currently index RTF documents on Windows.");
        }
        $sCommand = $this->findLocalCommand();
        
        // can't find the local command.
        if (empty($sCommand)) {
            return sprintf(_kt('Unable to find required command for indexing.  Please ensure that <strong>%s</strong> is installed and in the %d Path.  For more information on indexers and helper applications, please <a href="%s">visit the %d site</a>.'), $this->command, $this->support_url, APP_NAME);
        }
        
        return null;
    }
}

?>
