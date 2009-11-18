<?php
/**
 * $Id:$
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


class IndexingHelp
{
    
    /**
     * Method to get the help associated with an error name
     * @param string $name Error Name
     * @return string Help Message
     */
    public function getHelp($name)
    {
        // Remove all nonword characters
        // 'Open Office Server' becomes 'OpenOfficeServer'
        $function = '__'.preg_replace('/\W+/', '', $name);
        
        
        if (method_exists('IndexingHelp', $function)) {
            return IndexingHelp::$function();
        } else {
            return 'no help for '.$name;
        }
    }
    
    /**
     * Method to get help associated with Open Office Server
     * @return string help message
     */
    private function __OpenOfficeServer()
    {
        $str = _kt('This could be due to OpenOffice.org not having been started, OR not installed');
        
        $str .= '</li><li>';
        
        $str .= _kt('For more information, please see');
        $str .= ': <a href="http://wiki.knowledgetree.com/Troubleshooting_OpenOffice.org_Service">http://wiki.knowledgetree.com/Troubleshooting_OpenOffice.org_Service</a>';
        
        return $str;
    }
    
    /**
     * Method to get help associated with Document Indexer Service
     * @return string help message
     */
    private function __DocumentIndexer()
    {
        $str = _kt('This could be due to the indexer not having been started, OR not configured correctly');
        
        $str .= '</li><li>';
        
        $str .= _kt('For more information, please see');
        $str .= ': <a href="http://wiki.knowledgetree.com/Troubleshooting_the_Document_Indexer">http://wiki.knowledgetree.com/Troubleshooting_the_Document_Indexer</a>';
        
        return $str;
    }
    
    /**
     * Method to get the list of affected filetypes when an extractor is not available
     * @param string $extractor Name of the Extractor
     * @return string List of Affected filetypes
     */
    public function affectedTypes($extractor)
    {
        // Create SQL
        $sql = "SELECT filetypes FROM mime_types INNER JOIN mime_extractors ON (extractor_id = mime_extractors.id)
            WHERE mime_extractors.name = ?";
        
        // Create Query
        $aQuery = array($sql, array($extractor));
        
        // Get Results from DB
        $aTempRes = DBUtil::getResultArray($aQuery);
        
        $str = '';
        
        // Prepare into a string
        if (count($aTempRes) > 0) {
            $comma = '';
            foreach ($aTempRes as $mime)
            {
                $str .= $comma.$mime['filetypes'];
                $comma = ', ';
            }
        }
        
        return $str;
    }
}

?>
