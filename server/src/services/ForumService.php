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
require 'ForumServicePDO.php';

// Slim Framework Route Mappings
$app->get('/forums/admin', 'ForumService::getAllForums');
$app->get('/forums/admin/:id', 'ForumService::getForum');
$app->post('/forums/admin', 'ForumService::createForum');
$app->put('/forums/admin/:id', 'ForumService::updateForum');
$app->delete('/forums/admin/:id', 'ForumService::deleteForum');

$app->get('/forums/enrollment/:forumId/:userId','ForumService::getForumEnrollmentStatus');
$app->post('/forums/enrollment', 'ForumService::setForumEnrollmentStatus');
$app->get('/forums/enrollment/all', 'ForumService::getAllForumEnrollment');
$app->get('/forums/user/:userId', 'ForumService::getForumsForUser');

// Decided not to allow a path of parent IDs because it precludes the use of
// urls that have IDs in the middle like /forums/:forumId/log
// $app->get('/forums/:parentIds+', 'ForumService::getFileNodes');
$app->get('/forums/:parentId', 'ForumService::getFileNodes');
$app->post('/forums/folder', 'ForumService::createFileNode');
$app->delete('/forums/folder/:id', 'ForumService::deleteFileNode');
$app->delete('/forums/file/:id', 'ForumService::deleteFileNode');

$app->get('/forums/:forumId/log', 'ForumService::getForumLog');
$app->post('/forums/log', 'ForumService::createForumLogEntry');
$app->delete('/forums/:forumId/log', 'ForumService::purgeForumLog');

/**
 * ForumService
 *
 * Forum Services API
 * Some of these methods will be removed once the application is operational and
 * implements business rules
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumService
{

   /**
    *
    * @see ForumServicePDO::getAllForums()
    */
   public static function getAllForums()
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $forums = $pdo->getAllForums();
         AppUtils::sendResponse($forums);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error getting all forums", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getForum()
    */
   public static function getForum($id)
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $forum = $pdo->getForum();
         AppUtils::sendResponse($forum);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting forum with ID: " . $id, $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::createForum()
    */
   public static function createForum()
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         $forumData = (array) json_decode($body);
         $pdo = new ForumServicePDO();
         $forumId = $pdo->createForum($forumData['name'], $forumData['userId']);
         AppUtils::sendResponse($forumId);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error creating forum", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::updateForum()
    */
   public static function updateForum($id)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         $forum = $pdo->getForum($id);
         if (isset($forum))
         {
            // get and decode JSON request body
            $request = $app->request();
            $body = $request->getBody();
            $forumData = json_decode($body);
            
            $pdo->updateForum($id, (array) $forumData);
            AppUtils::sendResponse($forumData);
         }
         else
         {
            AppUtils::sendError(AppUtils::USER_ERROR_CODE, 
               "Forum with ID $id does not exist!", "Database update failure");
         }
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error updating forum with ID: " . $id, $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::deleteForum()
    */
   public static function deleteForum($id)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new ForumServicePDO();
         $pdo->deleteForum($id);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error deleting forum with ID: " . $id, $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::setForumEnrollmentStatus()
    */
   public static function setForumEnrollmentStatus($id)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         $params = (array) json_decode($body);
         $userId = $params['userId'];
         $forumId = $params['forumId'];
         $eStatus = $pdo->setForumEnrollmentStatus($params['forumId'], 
            $params['userId'], $params['enrollmentStatus']);
         AppUtils::sendResponse($eStatus);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error setting enrollment status for user $userId in forum $forumId", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getForumEnrollmentStatus()
    */
   public static function getForumEnrollmentStatus($forumId, $userId)
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $eStatus = $pdo->getForumEnrollmentStatus($forumId, $userId);
         AppUtils::sendResponse($eStatus);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting enrollment status for user $userId in forum $forumId", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getAllForumEnrollment()
    */
   public static function getAllForumEnrollment()
   {
      try
      {
         $pdo = new ForumServicePDO();
          
         $result = $pdo->getAllForumEnrollment();
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(),
         "Error getting enrollment status for user $userId in forum $forumId",
         $e->getMessage());
      }
      }
       
   /**
    *
    * @see ForumServicePDO::getForumsForUser()
    */
   public static function getForumsForUser($userId)
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $forums = $pdo->getForumsForUser($userId);
         AppUtils::sendResponse($forums);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting forums for user $userId", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getForumLog()
    */
   public static function getForumLog($forumId)
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $logs = $pdo->getForumLog($forumId);
         AppUtils::sendResponse($logs);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting log for forum $forumId", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::createForumLogEntry()
    */
   public static function createForumLogEntry()
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         $forumLog = json_decode($body);
         $pdo = new ForumServicePDO();
         $forumLogId = $pdo->createForumLogEntry((array) $forumLog);
         AppUtils::sendResponse($forumLogId);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error creating forum log entry", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::purgeForumLog()
    */
   public static function purgeForumLog($forumId)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new ForumServicePDO();
         $pdo->purgeForumLog($forumId);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error purging log for forum $forumId", $e->getMessage());
      }
   }

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
         $newNode = $pdo->createFileNode((array) $node);
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
    * @see ForumServicePDO::deleteFileNode()
    */
   public static function deleteFileNode($id)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new ForumServicePDO();
         $pdo->deleteFileNode($id);
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