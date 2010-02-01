<?php
/**
 * $Id$
 *
 * Small non-domain-specific utility functions
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

/**
 *  Class that creates printer friendly version of any referer page
 *  Prints any content, and has ability to attach css styles to the printing context
 **/
class printer {
	/**
	 * This is the context of the document to be printed
	 *
	 * @var string $_context
	 */
	var $_context = 0;
	
	/**
	 * Prep string for line endings
	 *
	 * @param string $str
	 * @return string
	 */

    private function _prepareString($str)
    {
        $str = str_replace("\r", '', $str);
        $str = str_replace("\n", '\n', $str);
        $str = str_replace("\047", "\\'", $str);
        $str = str_replace('"', '\"', $str);
        return $str;
    }
    /**
     * Referer type function call to get the body
     * of a document to be printed 
     *
     * @param file $fname
     * @return string
     */

    function readBody($fname)
    {
        $html = '';
        ob_start();
        if (@readfile($fname))$html = ob_get_contents();
        
        ob_end_clean();
        if (preg_match('#<body[^>]*>(.+?)</body>#is', $html, $matches)) {
            $html = $matches[1];
        }        
        return $html;
    }
    
    /**
     * Main context for printing. Consits of the Css for layout
     * and javascript for the print window
     *
     * @param string $printHtml
     * @param string $stylesheet
     * @return string context
     */

    function addPrintContext($printHtml, $stylesheet = array())
    {
        $this->_context += 1;
        $printHtml = $this->_prepareString($printHtml);
        echo '<!-- PRINTING CONTEXT ' . $this->_context . ' -->' . "\n";
        echo '<style type="text/css">' . "\n";
        echo '@media print {' . "\n";
        echo '    #KTScreenOut' . $this->_context . ' { display: none; }' . "\n";
        echo '    #print_ico { display: none; }' . "\n";
        echo '    head title { display: none; }' . "\n";
        foreach ($stylesheet as $k => $v) {
            echo '    ' . $k . ' { ' . $v . '}' . "\n";
        }
        echo '}' . "\n";
        echo '@media screen {' . "\n";
        echo '    #KTPrintOut' . $this->_context . ' { display: none; }' . "\n";
        echo '}' . "\n";
        echo '</style>' . "\n";
        echo '<script type="text/javascript" language="JavaScript">' . "\n";
        echo 'function KT_GoPrint_' . $this->_context . '()' . "\n";
        echo '{' . "\n";
        echo '    document.body.innerHTML = \'<div id="KTScreenOut' . $this->_context . '">\' + document.body.innerHTML + \'<\/div>\';' . "\n";
        echo '    document.body.innerHTML += \'<div id="KTPrintOut' . $this->_context . '">' . $printHtml . '<\/div>\';' . "\n";
        echo '    window.print();' . "\n";
        echo '}' . "\n";
        echo '</script>' . "\n";
        echo '<!-- END OF PRINTING CONTEXT ' . $this->_context . ' -->' . "\n";
        return $this->_context;
    }
    /**
     * Add show print link on page
     *
     * @param string $context
     * @param string $linkText
     * @param string $attributes
     */

    function showPrintLink($context, $linkText, $attributes = '')
    {
    	$hostPath = KTUtil::kt_url() . '/resources/graphics/printer.gif';
    
        echo '<a href="javascript:KT_GoPrint_' . $context . '()"' . (!empty($attributes) ? ' ' . $attributes : '') . '> 
        <img src="'.$hostPath.'"align="left" title="'.$linkText.'" border="0" id="print_ico"/></a>';
    }
    /**
     * Add show print button
     *
     * @param string $context
     * @param string $buttonText
     * @param string $attributes
     */

    function showPrintButton($context, $buttonText, $attributes = '')
    {
        echo '<input type="button" value="' . $buttonText . '"' . (!empty($attributes) ? ' ' . $attributes : '') . ' onclick="KT_GoPrint_' . $context . '()" />';
    }
	
}
?>