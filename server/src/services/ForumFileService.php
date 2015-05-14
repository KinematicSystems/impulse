<?php
/**
 * imPulse(R) - Group Collaboration Software
 *
 * @author      Matt Grippaldi <mattg@kinematicsystems.com>
 * @copyright   2013 Kinematic Systems LLC
 * @link        http://www.kinematicsystems.com/impulse
 * @license     http://www.kinematicsystems.com/impulse/license.html
 * @version     2.0.0
 * @package     imPulse
 *
 * LICENSE
 *
 * This file is part of imPulse.
 *
 * imPulse is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * imPulse is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with imPulse.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The imPulse logo is a USTPO registered trademark #4172918
 *
 */
require_once 'ForumServicePDO.php';

// Slim Framework Route Mappings
// Decided not to allow a path of parent IDs because it precludes the use of
// urls that have IDs in the middle like /forums/:forumId/log
// $app->get('/forums/:parentIds+', 'ForumService::getFileNodes');
$app->get('/forum-files/:parentId', 'ForumFileService::getFileNodes');
$app->post('/forum-files/:parentId', 'ForumFileService::createFileNode');
$app->delete('/forum-files/forum/:forumId/node/:id', 'ForumFileService::deleteFileNode');
$app->get('/forum-files/file/:id', 'ForumFileService::getFileContent');
$app->put('/forum-files/:id', 'ForumFileService::renameFileNode');


/**
 * ForumFileService
 *
 * Forum File Services API
 * Some of these methods will be removed once the application is operational and
 * implements business rules
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumFileService
{
   /**
    *
    * @see ForumServicePDO::getFileNodes()
    */
   public static function getFileNodes($parentId)
   {
      try
      {
         $pdo = new ForumServicePDO();
         $nodes = $pdo->getFileNodes($parentId);
         
         AppUtils::sendResponse($nodes);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error retrieving file nodes for $parentId", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::createFileNode()
    */
   public static function createFileNode()
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         
         $node = json_decode($body);
         $pdo = new ForumServicePDO();
         $forumId = $node->forumId;
         $newNode = $pdo->createFileNode((array) $node);
         $newNode['changeType'] = ForumEvent::CREATE;
          
         AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, ForumEvent::NODE_CHANGE, 
            "Node created: " . $newNode['name'], $newNode);
         AppUtils::sendResponse($newNode);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error creating file node", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::renameFileNode()
    */
   public static function renameFileNode($id)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $params = $request->params();
 //        $body = $request->getBody();
 //        $params = (array) json_decode($body);
         // error_log(print_r($params, true));
         $name = $params['nodeName'];
         $forumId = $params['forumId'];
         $pdo = new ForumServicePDO();
         $newName = $pdo->renameFileNode($id, $name);
          
         $eventParams = array();
         $eventParams['changeType'] = ForumEvent::UPDATE;
         $eventParams['id'] = $id;
         $eventParams['name'] = $newName;
         $eventParams['forumId'] = $forumId;
          
          
         AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, ForumEvent::NODE_CHANGE, 
            "Renamed to " . $name, $eventParams);
         AppUtils::sendResponse($newName);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error renaming file node", 
            $e->getMessage());
      }
   }

   /**
    * Get content for a node and stream to browser
    *
    * @param string $id
    *           Forum File Node ID
    */
   public static function getFileContent($id)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         // Get the node info
         $pdo = new ForumServicePDO();
         $fileNode = $pdo->getFileNode($id);
         if (isset($fileNode))
         {
            $srcFile = FORUM_UPLOAD_DIR . $id;
            $res = $app->response();
            $res['Content-Description'] = 'File Transfer';
            $res['Content-Type'] = $fileNode['contentType']; // 'application/octet-stream';
                                                             // Content-Disposition:
                                                             // inline or
                                                             // attachement to
                                                             // force download
                                                             // regardless of
                                                             // type
            $res['Content-Disposition'] = 'inline; filename=' . $fileNode['name'];
            $res['Content-Transfer-Encoding'] = 'binary';
            $res['Expires'] = '0';
            $res['Cache-Control'] = 'must-revalidate';
            $res['Pragma'] = 'public';
            $res['Content-Length'] = filesize($srcFile);
            // echo "SOME DATA BYTES";
            readfile($srcFile);
         }
         else
         {
            AppUtils::sendError(AppUtils::DB_ERROR_CODE, 
               "Error getting content for file node with ID $id", 
               "Database Error");
         }
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting content for file node with ID $id", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::deleteFileNode()
    */
   public static function deleteFileNode($forumId,$id)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new ForumServicePDO();
         $pdo->deleteFileNode($id);
         $eventParams = array();
         $eventParams['id'] = $id;
         $eventParams['changeType'] = ForumEvent::DELETE;
         
         AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, ForumEvent::NODE_CHANGE, 
            "Node deleted id: " . $id, $eventParams);
         
         $app->response()->setStatus(204); // NO DOCUMENT STATUS CODE FOR
                                              // SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error deleting file node with ID $id", $e->getMessage());
      }
   }
}