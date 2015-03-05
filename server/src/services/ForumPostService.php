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
require_once 'ForumPostServicePDO.php';

// Slim Framework Route Mappings
$app->get('/forums/post/overviews', 'ForumPostService::getPostOverviews');
$app->get('/forums/:forumId/post', 'ForumPostService::getForumPost');
$app->get('/forums/:forumId/post/summary', 'ForumPostService::getPostSummary');
$app->get('/forums/:forumId/post/:id', 'ForumPostService::getPosting');
$app->post('/forums/post', 'ForumPostService::createForumPostEntry');
$app->put('/forums/post', 'ForumPostService::updateForumPostEntry');
$app->delete('/forums/post', 'ForumPostService::purgeForumPost');

/**
 * ForumPostService
 *
 * Forum Services API
 * Some of these methods will be removed once the application is operational and
 * implements business rules
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumPostService
{

   /**
    *
    * @see ForumPostServicePDO::getForumPost()
    */
   public static function getForumPost($forumId)
   {
      try
      {
         $pdo = new ForumPostServicePDO();
         
         $posts = $pdo->getForumPost($forumId);
         AppUtils::sendResponse($posts);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting post for forum $forumId", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumPostServicePDO::getPosting()
    */
   public static function getPosting($forumId, $id)
   {
      try
      {
         $pdo = new ForumPostServicePDO();
         
         $posting = $pdo->getPosting($forumId, $id);
         AppUtils::sendResponse($posting);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting forum post with ID $id for forum $forumId", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumPostServicePDO::getPostSummary()
    */
   public static function getPostSummary($forumId)
   {
      try
      {
         $pdo = new ForumPostServicePDO();
         
         $summary = $pdo->getPostSummary($forumId);
         AppUtils::sendResponse($summary);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting forum post summary for forum $forumId", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumPostServicePDO::getPostOverviews()
    */
   public static function getPostOverviews()
   {
      try
      {
         $pdo = new ForumPostServicePDO();
         $userId = AppUtils::getUserId();
         $results = $pdo->getPostOverviews($userId);
         AppUtils::sendResponse($results);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(),
         "Error getting forum post overviews for user ID $userId",
         $e->getMessage());
      }
      }
       
   /**
    *
    * @see ForumPostServicePDO::createForumPostEntry()
    */
   public static function createForumPostEntry()
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         $forumPost = (array) json_decode($body);
         $forumPost['userId'] = AppUtils::getUserId();
         $pdo = new ForumPostServicePDO();
         $forumPostId = $pdo->createForumPostEntry($forumPost);
         AppUtils::sendResponse($forumPostId);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error creating forum post entry", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumPostServicePDO::updateForumPostEntry()
    */
   public static function updateForumPostEntry()
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumPostServicePDO();
         $request = $app->request();
         $body = $request->getBody();
         $forumPost = (array) json_decode($body);
         $id = $forumPost['id'];
         
         $post = $pdo->getPosting($forumPost['forumId'], $id);
         if (isset($post))
         {
            $pdo->updateForumPostEntry($id, $forumPost);
            AppUtils::sendResponse(
               array(
                  "success" => true,
                  "message" => "Forum post with ID $id updated."
               ));
         }
         else
         {
            AppUtils::sendResponse(
               array(
                  "success" => false,
                  "message" => "Forum post with ID $id does not exist!"
               ));
         }
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error updating forum post with ID $id", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumPostServicePDO::purgeForumPost()
    */
   public static function purgeForumPost($forumId)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new ForumPostServicePDO();
         $pdo->purgeForumPost($forumId);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error purging post for forum $forumId", $e->getMessage());
      }
   }
}