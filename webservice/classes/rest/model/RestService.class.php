<?php
/**
* Implements a cleaner wrapper restservice and webservice API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Webservice
* @version Version 0.1
*/


/**
 * Class Service - will act as a switch between
 * SOAP requests and REST requests
 *@author KnowledgeTree Team
 *@package Webservice
 *@version Version 0.9
 */
class RestService
{
    /**
     * Class construct
     *
     * @param object_type $object
     * @return jason encoded string
     */
    public function __construct($object)
    {
        try {

            $result = $this->runAsService($object);
            echo jason_encode(array('status_code' => 0,'result' => $result));

        } catch (Exception $e) {
            echo json_encode(array('status_code' => 1,'result' => $e->getMessage()));
        }
    }

    /**
     * Constructor for invoking a reflection object
     * Initiates the object as a service
     * @param class $object
     * @access private
     * @return class instance
     */

    public function getJason()
    {
         $result = $this->runAsService($object);
         return $result;

    }

    private function runAsService($object)
    {

        if (!isset($_GET['class'])) {

          throw new Exception('Method name not specified.');
        }

        $reflObject = new ReflectionObject($object);

        if (!$reflObject->hasMethod($_GET['class'])) {

          throw new Exception('There is no method with this name.');

        }

        $reflMethod = $reflObject->getMethod($_GET['method']);

        if ( !$reflMethod->isPublic() || $reflMethod->isStatic() || $reflMethod->isInternal() ) {

            throw new Exception('Invalid method name specified.');

        }

        $reflParameters = $reflMethod->getParameters();

        $args = array();


        foreach ($reflParameters as $param) {

         $paramName = $param->getName();

          if (!isset($_GET[$paramName])) {

            if ($param->isDefaultValueAvailable()) {

              $paramValue = $param->getDefaultValue();

            } else {

              throw new Exception('Required parameter "'.$paramName.'" is not specified.');

            }

          } else {

            $paramValue = $_GET[$paramName];

          }


          if ($param->getClass()) {

            throw new Exception('The method contains unsupported parameter type: class object.');

          }


          if ($param->isArray() && !is_array($paramValue)) {

            throw new Exception('Array expected for parameter "'.$paramName.'", but scalar found.');

          }


          $args[$param->getPosition()] = $paramValue;

        }

        return $reflMethod->invokeArgs($object, $args);

      }

}

?>