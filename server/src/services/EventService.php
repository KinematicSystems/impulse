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
require_once 'EventServicePDO.php';

// Slim Framework Route Mappings
$app->post('/events/:userId/:topic', 'EventService::subscribe');
$app->delete('/events/:userId/:topic', 'EventService::unsubscribe');
$app->get('/events/subscribers', 'EventService::getOnlineUsers');
$app->get('/events/subscriber/:userId', 'EventService::hasSubscriber');
$app->get('/events/:userId', 'EventService::popEvents');
$app->get('/events/:userId/:topic', 'EventService::popTopicEvents');

/**
 * EventService
 *
 * Event Service API
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class EventService
{
   
   /**
    *
    * @see EventServicePDO::popTopicEvents()
    */
   public static function popTopicEvents($userId, $topic)
   {
      try
      {
         $pdo = new EventServicePDO();
         
         $events = $pdo->popTopicEvents($userId, $topic);
         
         AppUtils::sendResponse($events);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting events for user $userId topic $topic", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see EventServicePDO::popEvents()
    */
   public static function popEvents($userId)
   {
      try
      {
         $pdo = new EventServicePDO();
         
         $events = $pdo->popEvents($userId);
         // TODO: Make event frames STOMP compliant
         // $stompMsg = 'MESSAGE\n'';
         // $stompMsg += 'sourceUserId:'.$event['sourceUserId'].'\n';
         AppUtils::sendResponse($events);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting events for user $userId", $e->getMessage());
      }
   }

   /**
    *
    * @see EventServicePDO::subscribe()
    */
   public static function subscribe($userId, $topic)
   {
      try
      {
         $pdo = new EventServicePDO();
         
         $pdo->subscribe($userId, $topic);
         
         AppUtils::sendResponse(
            array(
               "success" => true,
               "message" => "User $userId subscribed to topic $topic"
            ));
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error subscribing to events user $userId topic $topic", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see EventServicePDO::hasSubscriber()
    */
   public static function hasSubscriber($userId)
   {
      try
      {
         $pdo = new EventServicePDO();
         
         $result = $pdo->hasSubscriber($userId);
         
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error checking user subscription status", $e->getMessage());
      }
   }

   /**
    *
    * @see EventServicePDO::getOnlineUsers()
    */
   public static function getOnlineUsers()
   {
      try
      {
         $pdo = new EventServicePDO();
         
         $result = $pdo->getOnlineUsers();
         
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error getting online users", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see EventServicePDO::unsubscribe()
    */
   public static function unsubscribe($userId, $topic)
   {
      try
      {
         $pdo = new EventServicePDO();
         
         $pdo->unsubscribe($userId, $topic);
         
         AppUtils::sendResponse(
            array(
               "success" => true,
               "message" => "User $userId unsubscribed from topic $topic"
            ));
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error unsubscribing to events user $userId topic $topic", 
            $e->getMessage());
      }
   }
}

