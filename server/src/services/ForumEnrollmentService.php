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
$app->get('/forums/enrollment/:forumId/:userId', 
   'ForumEnrollmentService::getForumEnrollmentStatus');
$app->post('/forums/enrollment', 
   'ForumEnrollmentService::setForumEnrollmentStatus');
$app->get('/forums/enrollment/all', 
   'ForumEnrollmentService::getAllForumEnrollment');
$app->get('/forums/enrolled/:forumId', 
   'ForumEnrollmentService::getEnrolledForumUsers');
$app->get('/forums/not-enrolled/:forumId', 
   'ForumEnrollmentService::getNotEnrolledForumUsers');
$app->get('/forums/enroll/pending', 
   'ForumEnrollmentService::getPendingJoinRequests');
$app->get('/forums/enroll/invitations', 
   'ForumEnrollmentService::getInvitations');
$app->get('/forums/enroll/joined', 'ForumEnrollmentService::getForumsForUser');

/**
 * ForumEnrollmentService
 *
 * Forum Services API
 * Some of these methods will be removed once the application is operational and
 * implements business rules
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumEnrollmentService
{

   /**
    *
    * @see ForumServicePDO::setForumEnrollmentStatus()
    */
   public static function setForumEnrollmentStatus()
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         $params = (array) json_decode($body);
         // error_log(print_r($params, true));
         $userId = $params['userId'];
         $forumId = $params['forumId'];
         $eStatus = "";
         
         $forum = $pdo->getForum($forumId);
         $params['forumName'] = $forum['name'];
         $params['sourceUserId'] = AppUtils::getUserId();
         
         if ($params['enrollmentStatus'] == EnrollmentStatus::Invited)
         {
            $eStatus = $pdo->setForumEnrollmentStatus($params['forumId'], 
               $params['userId'], $params['enrollmentStatus']);
            // When status is set to invited send the invite user event
            AppUtils::sendEvent(UserEvent::DOMAIN, $userId, UserEvent::INVITE, 
               "Forum invitation", $params);
         }
         else 
            if ($params['enrollmentStatus'] == EnrollmentStatus::Accepted)
            {
               /*
                * When status is set to accepted subscribe to the forum events
                * and set the status to joined in the database
                */
               $params['enrollmentStatus'] = EnrollmentStatus::Joined;
               $eStatus = $pdo->setForumEnrollmentStatus($params['forumId'], 
                  $params['userId'], $params['enrollmentStatus']);
               
               $eventPdo = new EventServicePDO();
               $eventPdo->subscribe($userId, 
                  ForumEvent::DOMAIN . '.' . $forumId);
               
               AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
                  ForumEvent::ENROLLMENT, "Enrollment status changed", $params);
            }
            else 
               if ($params['enrollmentStatus'] == EnrollmentStatus::Left)
               {
                  /*
                   * When status is set to left (leaving) unsubscribe to the
                   * forum events and remove enrollment from the forum users
                   * list
                   */
                  $eStatus = $pdo->deleteForumEnrollment($params['forumId'], 
                     $params['userId']);
                  
                  $eventPdo = new EventServicePDO();
                  $eventPdo->unsubscribe($userId, 
                     ForumEvent::DOMAIN . '.' . $forumId);
                  
                  // Send the event to the user who left so they can update the
                  // UI
                  // because they will no longer receive forum events
                  AppUtils::sendEvent(UserEvent::DOMAIN, $userId, 
                     UserEvent::REMOVED, "Forum removed", $params);
                  
                  // Let all other forum members know that this user left the
                  // forum
                  AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
                     ForumEvent::ENROLLMENT, "Enrollment status changed", 
                     $params);
               }
               else
               {
                  $eStatus = $pdo->setForumEnrollmentStatus($params['forumId'], 
                     $params['userId'], $params['enrollmentStatus']);
                  AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
                     ForumEvent::ENROLLMENT, "Enrollment status changed", 
                     $params);
               }
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
   
   // TODO: figure out how to pass a static parameter with Slim to avoid this
   // $app->get('/forums/enrolled/:forumId',
   // 'ForumService::getEnrolledForumUsers');
   // $app->get('/forums/not-enrolled/:forumId',
   // 'ForumService::getNotEnrolledForumUsers');
   //
   /**
    *
    * @see ForumServicePDO::getForumEnrollment()
    */
   public static function getEnrolledForumUsers($forumId)
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $result = $pdo->getForumEnrollment($forumId, true);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting enrollment for forum $forumId", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getForumEnrollment()
    */
   public static function getNotEnrolledForumUsers($forumId)
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $result = $pdo->getForumEnrollment($forumId, false);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting enrollment for forum $forumId", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getPendingJoinRequests()
    */
   public static function getPendingJoinRequests()
   {
      try
      {
         $pdo = new ForumServicePDO();
         $userId = AppUtils::getUserId();
         $result = $pdo->getPendingJoinRequests($userId);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting pending join requests for user $userId", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getInvitations()
    */
   public static function getInvitations()
   {
      try
      {
         $pdo = new ForumServicePDO();
         $userId = AppUtils::getUserId();          
         $result = $pdo->getInvitations($userId);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(),
         "Error getting forum invitations for user $userId",
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
            "Error getting all forum enrollment", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getForumsForUser()
    */
   public static function getForumsForUser()
   {
      try
      {
         $pdo = new ForumServicePDO();
         $userId = AppUtils::getUserId();
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
}