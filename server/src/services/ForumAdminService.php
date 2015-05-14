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
$app->get('/forums', 'ForumAdminService::getAllForums');
$app->get('/forums/:id', 'ForumAdminService::getForum');
$app->post('/forums', 'ForumAdminService::createForum');
$app->put('/forums/:id', 'ForumAdminService::updateForum');
$app->delete('/forums/:id', 'ForumAdminService::deleteForum');

/**
 * ForumAdminService
 *
 * Forum Services Admin API
 * Some of these methods will be removed once the application is operational and
 * implements business rules
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumAdminService
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
         
         $forum = $pdo->getForum($id);
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
         $forumId = $pdo->createForum($forumData['name'], $forumData['description'], $forumData['userId']);

         // Create a subscription automagically for the user who created the forum
         // Moved to client 4/29/2014
         //$eventPdo = new EventServicePDO();
         //$eventPdo->subscribe($forumData['userId'], ForumEvent::DOMAIN . '.' . $forumId);
          
         $params = array(
            'forumId' => $forumId,
            'changeType' => ForumEvent::CREATE
         );
          
         // No point in sending this nobody is listening until after this returns
         // 4/29/2014
         //AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, ForumEvent::CHANGE,
         //"Forum created: " . $forumData['name'], $params);

         AppUtils::sendEvent(UserEvent::DOMAIN, $forumData['userId'], UserEvent::JOINED,
         "Forum joined via creation: " . $forumData['name'], $params);
                  
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
            $params = (array) $forumData;
            $params['id'] = $id;
            $pdo->updateForum($id, $params, AppUtils::getUserId());
            
            $params['changeType'] = ForumEvent::UPDATE;
            AppUtils::sendEvent(ForumEvent::DOMAIN, $id, ForumEvent::CHANGE, 
               "Forum updated: " . $params['name'], $params);
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
      $params = array(
         'forumId' => $id,
         'changeType' => ForumEvent::DELETE
      );
      
      try
      {
         $pdo = new ForumServicePDO();
         $pdo->deleteForum($id);
         AppUtils::sendEvent(ForumEvent::DOMAIN, $id, ForumEvent::CHANGE, 
            "Forum deleted: " . $id, $params);
         
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error deleting forum with ID: " . $id, $e->getMessage());
      }
   }
}