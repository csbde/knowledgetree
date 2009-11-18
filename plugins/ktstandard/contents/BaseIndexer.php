<?php
/**
 * $Id$
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
 *
 */

class KTBaseIndexerTrigger {
    /**
     * Which MIME types that this indexer acts upon.
     */
    var $mimetypes = array(
       // 'text/plain' => true,
    );

    /**
     * commandconfig is where to find the command to use in the
     * KnowledgeTree configuration.  For example, it may be
     * "indexing/catdoc", which would correspond to the "indexing"
     * section of config.ini, item "catdoc".
     */
    var $commandconfig = '';    // Something like "indexing/catdoc"

    /**
     * In the absence of the command in the configuration, what command
     * to use directly.
     */
    var $command = '';          // Something like "catdoc"

    /**
     * Output of the command
     */
    var $aCommandOutput = array();


    /**
     * Any options to send to the command before the input file.
     */
    var $args = array();
    var $support_url = 'http://wiki.knowledgetree.com/Document_Indexers';

    /**
     * Setting use_pipes to true will cause the output of the command to
     * be sent to a temporary file created and chosen by the system.
     *
     * If it is false, the temporary file will be sent as the last
     * parameter.
     */
    var $use_pipes = true;

    /* return a diagnostic string _if_ there is something wrong.  NULL otherwise. */
    function getDiagnostic() {
        return null;
    }

    function setDocument($oDocument) {
        $this->oDocument = $oDocument;
    }

    function transform() {
        $iMimeTypeId = $this->oDocument->getMimeTypeId();
        $sMimeType = KTMime::getMimeTypeName($iMimeTypeId);
        if (!array_key_exists($sMimeType, $this->mimetypes)) {
            return;
        }

        $oStorage = KTStorageManagerUtil::getSingleton();
        $sFile = $oStorage->temporaryFile($this->oDocument);

        $tempstub = 'transform';
        if ($this->command != null) {
            $tempstub = $this->command;
        }

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");

        $myfilename = tempnam($sBasedir, 'kt.' . $tempstub);
        if (OS_WINDOWS) {
            $intermediate = tempnam($sBasedir, 'kt.' . $tempstub);
            if (!@copy($sFile, $intermediate)) {
                return ;
            }
        } else {
            $intermediate = $sFile;
        }

        $contents = $this->extract_contents($intermediate, $myfilename);

        @unlink($myfilename);
        if (OS_WINDOWS) { @unlink($intermediate); }
        if (empty($contents)) {
            return;
        }
        $aInsertValues = array(
            'document_id' => $this->oDocument->getId(),
            'document_text' => $contents,
        );
        $sTable = KTUtil::getTableName('document_text');

        // clean up the document query "stuff".
        // FIXME this suggests that we should move the _old_ document_searchable_text across to the old-document's id if its a checkin.
        DBUtil::runQuery(array('DELETE FROM ' . $sTable . ' WHERE document_id = ?', array($this->oDocument->getId())));
        DBUtil::autoInsert($sTable, $aInsertValues, array('noid' => true));

    }

    // handles certain, _very_ simple reader types.
    function extract_contents($sFilename, $sTempFilename) {
        $sCommand = KTUtil::findCommand($this->commandconfig, $this->command);
        if (empty($sCommand)) {
            return false;
        }

        $cmdline = array($sCommand);
        $cmdline = kt_array_merge($cmdline, $this->args);
        $cmdline[] = $sFilename;

        $aOptions = array();
        $aOptions['exec_wait'] = 'true';
        if ($this->use_pipes) {
            $aOptions["append"] = $sTempFilename;
        } else {
            $cmdline[] = $sTempFilename;
        }

        $aRet = KTUtil::pexec($cmdline, $aOptions);

        $this->aCommandOutput = $aRet['out'];
        $contents = file_get_contents($sTempFilename);

        return $contents;
    }
}

?>
