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

$app->get('/enrollment/:forumId/status/:userId', 
   'ForumEnrollmentService::getForumEnrollmentStatus');
$app->post('/enrollment/:forumId/status/:userId', 
   'ForumEnrollmentService::setForumEnrollmentStatus');
$app->post('/enrollment/:forumId/accept/:userId', 
   'ForumEnrollmentService::acceptInvite');
$app->post('/enrollment/:forumId/leave/:userId', 
   'ForumEnrollmentService::leaveForum');
$app->post('/enrollment/:forumId/approve/:userId', 
   'ForumEnrollmentService::approveJoinRequest');
$app->get('/enrollment', 'ForumEnrollmentService::getAllForumEnrollment');
$app->get('/enrollment/:forumId', 'ForumEnrollmentService::getForumEnrollment');
$app->get('/enrollment/:forumId/users/enrolled', 
   'ForumEnrollmentService::getEnrolledUsers');
$app->get('/enrollment/:forumId/users/for-invite', 
   'ForumEnrollmentService::getUsersForInvite');
$app->get('/enrollment/:userId/pending-join-requests', 
   'ForumEnrollmentService::getPendingJoinRequests');
$app->get('/enrollment/:userId/rejections', 
   'ForumEnrollmentService::getRejections');
$app->get('/enrollment/:userId/invitations', 
   'ForumEnrollmentService::getInvitations');
$app->get('/enrollment/:userId/joined', 
   'ForumEnrollmentService::getForumsForUser');
$app->get('/enrollment/:userId/enrolled', 'ForumEnrollmentService::getEnrolled');
$app->delete('/enrollment/:forumId/user/:userId', 
   'ForumEnrollmentService::deleteForumEnrollment');

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
   public static function setForumEnrollmentStatus($forumId)
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
         $eStatus = "";
         
         // AppUtils::logDebug("forumId: $forumId userId: $userId");
         $forum = $pdo->getForum($forumId);
         $params['forumName'] = $forum['name'];
//        $params['sourceUserId'] = AppUtils::getUserId();
         
         if ($params['enrollmentStatus'] == EnrollmentStatus::Invited)
         {
            $eStatus = $pdo->setForumEnrollmentStatus($params['forumId'], 
               $params['userId'], $params['enrollmentStatus']);
            // When status is set to invited send the invite user event
            // and a forum event so that all other forum members will see that
            // the user has been invited
            AppUtils::sendEvent(UserEvent::DOMAIN, $userId, UserEvent::INVITE, 
               "Forum invitation", $params);
            
            AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
               ForumEvent::ENROLLMENT, "User invited to forum", $params);
         }
         else if ($params['enrollmentStatus'] == EnrollmentStatus::Rejected)
         {
            /*
             * When status is set to rejected let the user know on the user
             * topic and let all the other forum members know
             */
            $eventPdo = new EventServicePDO();
            $eStatus = $pdo->setForumEnrollmentStatus($params['forumId'], 
               $params['userId'], $params['enrollmentStatus']);
            
            // Send the event to the user who was rejected
            AppUtils::sendEvent(UserEvent::DOMAIN, $userId, UserEvent::REJECTED, 
               "Join request rejected", $params);
            
            // Let all other forum members know that this user was rejected
            AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
               ForumEvent::ENROLLMENT, "Join request rejected", $params);
         }
         else
         {
            $eStatus = $pdo->setForumEnrollmentStatus($params['forumId'], 
               $params['userId'], $params['enrollmentStatus']);
            AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
               ForumEvent::ENROLLMENT, "Enrollment status changed", $params);
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
    * Accept a forum invitation
    *
    * @see ForumServicePDO::setForumEnrollmentStatus()
    */
   public static function acceptInvite($forumId, $userId)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         
         // AppUtils::logDebug("forumId: $forumId userId: $userId");
         $forum = $pdo->getForum($forumId);
         $params = array();
         $params['forumId'] = $forumId;
         $params['userId'] = $userId;
         $params['forumName'] = $forum['name'];
//         $params['sourceUserId'] = AppUtils::getUserId();
         $params['enrollmentStatus'] = EnrollmentStatus::Joined;
         
         /*
          * When accepted an invitation set the status to joined in the
          * database.
          */
         $eStatus = $pdo->setForumEnrollmentStatus($forumId, $userId, 
            $params['enrollmentStatus']);
         
         // Notify the invitee that they have been joined successfully
         AppUtils::sendEvent(UserEvent::DOMAIN, $userId, UserEvent::JOINED, 
            "User joined forum", $params);
         
         // Notify forum members that user accepted invite
         AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
            ForumEvent::ENROLLMENT, "User accepted invite", $params);
         AppUtils::sendResponse($eStatus);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error accepting invitation for user $userId in forum $forumId", 
            $e->getMessage());
      }
   }

   /**
    * Approve a join request
    *
    * @see ForumServicePDO::setForumEnrollmentStatus()
    */
   public static function approveJoinRequest($forumId, $userId)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         
         // AppUtils::logDebug("forumId: $forumId userId: $userId");
         $forum = $pdo->getForum($forumId);
         $params = array();
         $params['forumId'] = $forumId;
         $params['userId'] = $userId;
         $params['forumName'] = $forum['name'];
 //        $params['sourceUserId'] = AppUtils::getUserId();
         $params['enrollmentStatus'] = EnrollmentStatus::Joined;
         
         /*
          * When join request is approved set the status to joined in the
          * database.
          */
         $eStatus = $pdo->setForumEnrollmentStatus($forumId, $userId, 
            $params['enrollmentStatus']);
         
         // Notify the invitee that they have been joined successfully
         AppUtils::sendEvent(UserEvent::DOMAIN, $userId, UserEvent::JOINED, 
            "User joined forum", $params);
         
         // Notify forum members that user join request has been approved
         AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
            ForumEvent::ENROLLMENT, "User join request approved", $params);
         AppUtils::sendResponse($eStatus);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error accepting invitation for user $userId in forum $forumId", 
            $e->getMessage());
      }
   }

   /**
    * Leave forum
    *
    * @see ForumServicePDO::setForumEnrollmentStatus()
    */
   public static function leaveForum($forumId, $userId)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         
         $forum = $pdo->getForum($forumId);
         $params = array();
         $params['forumId'] = $forumId;
         $params['userId'] = $userId;
         $params['forumName'] = $forum['name'];
//         $params['sourceUserId'] = AppUtils::getUserId();
         $params['enrollmentStatus'] = EnrollmentStatus::Left;
         
         /*
          * When status is set to left (leaving) unsubscribe to the forum events
          * and remove enrollment from the forum users list
          */
         $eventPdo = new EventServicePDO();
         $eventPdo->unsubscribe($userId, ForumEvent::DOMAIN . '.' . $forumId);
         
         $eStatus = $pdo->setForumEnrollmentStatus($forumId, $userId, 
            $params['enrollmentStatus']);
         
         // Send the event to the user who left so they can update the
         // UI because they will no longer receive forum events
         AppUtils::sendEvent(UserEvent::DOMAIN, $userId, UserEvent::REMOVED, 
            "Forum removed", $params);
         
         // Let all other forum members know that this user left the
         // forum
         AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, 
            ForumEvent::ENROLLMENT, "User has left forum", $params);
         AppUtils::sendResponse($eStatus);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error leaving forum for user $userId in forum $forumId", 
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
    * @see ForumServicePDO::getForumEnrollment()
    */
   public static function getForumEnrollment($forumId)
   {
      try
      {
         $pdo = new ForumServicePDO();
         
         $result = $pdo->getForumEnrollment($forumId);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting enrollment for forum $forumId", 
            $e->getMessage());
      }
   }
   
   /*
    * public static function getByParam($forumId) { $app =
    * \Slim\Slim::getInstance(); $enrolled =
    * $app->request()->params('enrolled'); if (!isset($enrolled)) {
    * AppUtils::sendError('', __METHOD__, "Missing enrolled parameter"); return;
    * } $enrolled = ($enrolled === 'true'); try { $pdo = new ForumServicePDO();
    * $result = $pdo->getForumEnrollment($forumId, $enrolled);
    * AppUtils::sendResponse($result); } catch (PDOException $e) {
    * AppUtils::logError($e, __METHOD__); AppUtils::sendError($e->getCode(),
    * "Error getting enrollment for forum $forumId", $e->getMessage()); } }
    */
   
   /**
    * Get list of users eligible for forum invitation
    *
    * @see ForumServicePDO::getUsersForInvite()
    */
   public static function getUsersForInvite($forumId)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         
         $result = $pdo->getUsersForInvite($forumId);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error users eligible for invitation for forum $forumId", 
            $e->getMessage());
      }
   }

   /**
    * Get the users who are joined to the specified forum
    *
    * @see ForumServicePDO::getEnrolledUsers()
    */
   public static function getEnrolledUsers($forumId)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new ForumServicePDO();
         
         $result = $pdo->getEnrolledUsers($forumId);
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
    * @see ForumServicePDO::getEnrolled()
    */
   public static function getEnrolled()
   {
      try
      {
         $pdo = new ForumServicePDO();
         $userId = AppUtils::getUserId();
         $result = $pdo->getEnrolled($userId);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting joined enrollment for user $userId", $e->getMessage());
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
            "Error getting forum invitations for user $userId", $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::getRejections()
    */
   public static function getRejections()
   {
      try
      {
         $pdo = new ForumServicePDO();
         $userId = AppUtils::getUserId();
         $result = $pdo->getRejections($userId);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting invitation rejections for user $userId", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see ForumServicePDO::deleteForumEnrollment()
    */
   public static function deleteForumEnrollment($forumId, $userId)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new ForumServicePDO();
         
         $pdo->deleteForumEnrollment($forumId, $userId);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error deleting forum enrollment $forumId, $userId", 
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