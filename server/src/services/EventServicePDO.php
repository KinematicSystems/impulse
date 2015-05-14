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

/**
 * EventServicePDO
 *
 * Event Queue Data Access Object
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class EventServicePDO
{
   private $db;

   /**
    * Constructor
    */
   public function __construct()
   {
      $this->db = new NotORM(getPDO());
   }

   public function initSubscriptionsForUser($userId)
   {
      // User Directed Events
      $this->subscribe($userId, 'USER.' . $userId);
      
      $pdo = new ForumServicePDO();
      $forums = $pdo->getForumsForUser($userId);
      
      foreach ($forums as $forum)
      {
        $this->subscribe($userId, 'FORUM.' . $forum['id']);
      }
   }

   public function subscribe($userId, $topic)
   {
      // Setting the session id so that if the session times out and the record
      // is removed from
      // the session_data table the deletion can cascade to event descriptions
      try
      {
         $sub = array(
            "userId" => $userId,
            "topic" => $topic,
            "session_id" => AppUtils::getSessionId()
         );

         $this->db->event_subscriptions()->insert($sub);
      }
      catch (PDOException $e)
      {
         if (((int) $e->getCode()) != 23000) // Duplicate Key so OK
         {
             AppUtils::logError($e, __METHOD__);
             throw $e;
         }
      }
   }

   public function unsubscribe($userId, $topic)
   {
      // This will CASCADE to event table via schema
      $pdo = getPDO();
      $sql = "DELETE FROM event_subscriptions WHERE `userId` = :userId AND `topic` = :topic";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
      $stmt->bindParam(':topic', $topic, PDO::PARAM_STR);
      $stmt->execute();
   }

   public function unsubscribeForUser($userId)
   {
      // This will CASCADE to event table via schema
      $pdo = getPDO();
      $sql = "DELETE FROM event_subscriptions WHERE `userId` = :userId";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
      $stmt->execute();
   }

   public function getSubscribers($topic)
   {
      $subscribers = array();
      foreach ($this->db->event_subscriptions()->where("topic=?", $topic) as $row)
      {
         $subscribers[] = $row['userId']; // append
      }
      return $subscribers;
   }

   public function hasSubscriber($userId)
   {
      $subscriptions = array();
      foreach ($this->db->event_subscriptions()->where("userId=?", $userId) as $row)
      {
         $subscriptions[] = $row['userId']; // append
      }
      return (count($subscriptions) > 0);
   }

   public function getOnlineUsers()
   {
      AppUtils::purgeExpiredSessions();
      
      try
      {
         $pdo = getPDO();
         $sql = "SELECT DISTINCT(userId) FROM event_subscriptions";
         $stmt = $pdo->query($sql);
         $results = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
         return $results;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   public function pushEvent($sourceUserId, $topic, $content)
   {
      $eventRecord = array(
         'sourceUserId' => $sourceUserId,
         'topic' => $topic,
         'content' => $content,
         'userId' => ''
      );
      
      $subscribers = $this->getSubscribers($topic);
      foreach ($subscribers as $subscriber)
      {
         $eventRecord['userId'] = $subscriber;
         $this->db->event_queue()->insert($eventRecord);
      }
   }

   public function popEvents($userId)
   {
      try
      {
         $events = AppUtils::dbToArray(
            $this->db->event_queue()->where("userId=?", $userId));
         
         $pdo = getPDO();
         $sql = "DELETE FROM event_queue WHERE `userId` = :userId";
         $stmt = $pdo->prepare($sql);
         $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
         $stmt->execute();
         
         return $events;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   public function popTopicEvents($userId, $topic)
   {
      try
      {
         $events = AppUtils::dbToArray(
            $this->db->event_queue()->where("userId=? AND topic=?", $userId, 
               $topic));
         
         $pdo = getPDO();
         $sql = "DELETE FROM event_queue WHERE `userId` = :userId AND `topic` = :topic";
         $stmt = $pdo->prepare($sql);
         $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
         $stmt->bindParam(':topic', $topic, PDO::PARAM_STR);
         $stmt->execute();
         
         return $events;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }
}    





