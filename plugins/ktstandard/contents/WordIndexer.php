<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTWordIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/msword' => true,
    );
    var $command = 'catdoc';          // could be any application.
    var $commandconfig = 'indexer/catdoc';          // could be any application.
    var $args = array("-w", "-d", "UTF-8");
    var $use_pipes = true;
    
    function extract_contents($sFilename, $sTempFilename) {
        if (OS_WINDOWS) {	    
            $this->command = 'c:\antiword\antiword.exe';
            $this->commandconfig = 'indexer/antiword';
            $this->args = array();
        }
	  putenv('LANG=en_US.UTF-8');

	    $sCommand = KTUtil::findCommand($this->commandconfig, $this->command);
        if (empty($sCommand)) {
            return false;
        }
        
        if (OS_WINDOWS) {	
            $sDir = dirname(dirname($sCommand));
	          putenv('HOME=' . $sDir);

	        /*
            $cmdline = array($sCommand);
            $cmdline = kt_array_merge($cmdline, $this->args);
            $cmdline[] = $sFilename;
            
            $sCmd = KTUtil::safeShellString($cmdline);
        	$sCmd .= " >> " . escapeshellarg($sTempFilename);
        	
        	$sCmd = str_replace( '/','\\',$sCmd);
        	
            $sCmd = "start /b \"kt\" " . $sCmd;
            
            pclose(popen($sCmd, 'r'));
        	
            $this->aCommandOutput = 1;
            $contents = file_get_contents($sTempFilename);
            return $contents;
            */
        }
        return parent::extract_contents($sFilename, $sTempFilename);
    }
    
    function findLocalCommand() {
        if (OS_WINDOWS) {
            $this->command = 'c:\antiword\antiword.exe';
            $this->commandconfig = 'indexer/antiword';
            $this->args = array();
        }        
        $sCommand = KTUtil::findCommand($this->commandconfig, $this->command);
        return $sCommand;
    }
    
    function getDiagnostic() {
        $sCommand = $this->findLocalCommand();
        
        // can't find the local command.
        if (empty($sCommand)) {
            return sprintf(_kt('Unable to find required command for indexing.  Please ensure that <strong>%s</strong> is installed and in the %s Path.  For more information on indexers and helper applications, please <a href="%s">visit the %s site</a>.'), $this->command, APP_NAME, $this->support_url, APP_NAME);
        }
        
        return null;
    }
}

?>
