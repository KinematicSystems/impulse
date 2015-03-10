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
 * AppUtils
 *
 * This class has utiliy functions used throughout the server code.
 * All methods are static.
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class AppUtils
{
   const USER_ERROR_CODE = 98;
   const DB_ERROR_CODE = 99;
   private static $testMode = false;
   private static $stompMode = false;
   private static $testUserId = NULL;
   private static $testSessionId = "test-id-123";
   private static $messageTopic = '/topic/chat.general';
   private static $topic = '/topic/';
   private static $xactSession = NULL; // Reuse session within transaction
    
   /**
    * Send an http response with content encoded as JSON
    *
    * @param mixed $content           
    */
   public static function sendResponse($content)
   {
      $app = \Slim\Slim::getInstance();
      $app->response()->header('Content-Type', 'application/json');
      
      $jsonResponse = json_encode($content, JSON_NUMERIC_CHECK);
      // AppUtils::logDebug($jsonResponse);
      
      $jsonErr = json_last_error();
      if ($jsonErr != JSON_ERROR_NONE)
      {
         $errStr = '';
         switch ($jsonErr)
         {
            case JSON_ERROR_DEPTH:
               $errStr = ' - Maximum stack depth exceeded';
               break;
            case JSON_ERROR_STATE_MISMATCH:
               $errStr = ' - Underflow or the modes mismatch';
               break;
            case JSON_ERROR_CTRL_CHAR:
               $errStr = ' - Unexpected control character found';
               break;
            case JSON_ERROR_SYNTAX:
               $errStr = ' - Syntax error, malformed JSON';
               break;
            case JSON_ERROR_UTF8:
               $errStr = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
               break;
            default:
               $errStr = ' - Unknown error';
               break;
         }
         AppUtils::logDebug("JSON ERROR " . $errStr);
      }
      
      // Include support for JSONP requests
      if (!isset($_GET['callback']))
      {
         echo $jsonResponse;
      }
      else
      {
         echo $_GET['callback'] . '(' . $jsonResponse . ');';
      }
   }

   /**
    * Send an http response with 500 status and an error message and error code
    * formatted as JSON.
    *
    * @param int $code
    *           Error code
    * @param int $message
    *           Error message
    * @param int $status
    *           HTTP status code (default 500)
    */
   public static function sendError($code, $message, $details, $status = 500)
   {
      $app = \Slim\Slim::getInstance();
      $app->response()->header('Content-Type', 'application/json');
      // echo '{"error":{"text":' . $message . '", "errorCode":'. $status .
      // '}}';
      $app->response()->status($status);
      $msg = array(
         "error" => true,
         "code" => $code,
         "message" => $message,
         "details" => $details
      );
      
      echo json_encode($msg, JSON_NUMERIC_CHECK);
   }

   /**
    * Send a text message
    * Always call before sendResponse because the response will be
    * complete and stomp will throw exceptions in stompMode.
    *
    * @param String $msg           
    */
   public static function sendMessage($msg)
   {
      if (self::$stompMode)
      {
         $impulseHeader = array(
            "sourceUserId" => self::getUserId()
         );
         try
         {
            $stomp = new Stomp('tcp://localhost:61613', 'admin', 'password');
            $stomp->send(self::$messageTopic, $msg);
            unset($stomp);
         }
         catch (StompException $e)
         {
            self::logError($e, __METHOD__);
         }
      }
   }

   /**
    * Send a stomp collaboration event message to all or a single user
    * Always call before sendResponse because the response will be
    * complete and stomp will throw exceptions.
    *
    * @param String $topicId
    *           could be a userId for a forumId
    */
   public static function sendEvent($eventDomain, $topicId, $eventId, 
      $eventDescription, $eventParameters)
   {
      $impulseHeader = array(
         "sourceUserId" => self::getUserId(),
         "persistent" => 'false'
      );
      
      $content = array(
         "type" => $eventId,
         "description" => $eventDescription,
         "params" => $eventParameters
      );
      $msg = json_encode($content, JSON_NUMERIC_CHECK);
      
      if (self::$stompMode)
      {
         try
         {
            $stomp = new Stomp('tcp://localhost:61613', 'admin', 'password');
            $stomp->send(self::$topic . $topicId, $msg, $impulseHeader);
            unset($stomp);
         }
         catch (StompException $e)
         {
            self::logError($e, __METHOD__);
         }
      }
      else
      {
         $pdo = new EventServicePDO();
         $pdo->pushEvent(self::getUserId(), $eventDomain . '.' . $topicId, $msg);
      }
   }

   /**
    * Marshall a NotORM table/result to and array
    * Using this method because iterator_to_array will create
    * an associative array in many cases.
    *
    * @param mixed $result           
    * @return array Array containing row data from database table
    */
   public static function dbToArray($result)
   {
      $ret = array();
      foreach ($result as $item)
      {
         $ret[] = $item; // append
      }
      return $ret;
   }

   /**
    * Log an error to the PHP error log with class/method/line
    *
    * @param Exception $exception           
    * @param string $method
    *           usually __METHOD__
    */
   public static function logError($exception, $method)
   {
      $app = \Slim\Slim::getInstance();
      error_log(
         'ERROR: ' . $method . ' line ' . $exception->getLine() . ": " .
             $exception->getMessage());
   }

   /**
    * Log a debug message
    * Writing to the PHP error log for now.
    *
    * @param string $object           
    */
   public static function logDebug($object)
   {
      if (is_string($object))
         error_log('DEBUG: ' . $object);
      else
         error_log(print_r($object, TRUE));
   }

   /**
    * Generate a GUID
    *
    * @return string
    */
   public static function guid()
   {
      mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up.
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45); // "-"
      $uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen .
          substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen .
          substr($charid, 20, 12);
      return $uuid;
   }

   /**
    * Creates a 3 character salt sequence
    *
    * @return string
    */
   public static function createSALT()
   {
      $string = md5(uniqid(rand(), true));
      $salt = substr($string, 0, 3);
      
      return $salt;
   }
   
   /*
    * Session Based Login Functions
    */
   
   /**
    * Set to test mode because session will not be available
    *
    * @param
    *           boolean testMode true or false
    */
   public static function setTestMode($testMode)
   {
      self::$testMode = $testMode;
   }

   /**
    * Store validated user information in session
    *
    * @param string $userId           
    * @param string $accessLevel           
    */
   public static function setLoginValid($userId, $accessLevel)
   {
      if (self::$testMode)
      {
         self::$testUserId = $userId;
      }
      else
      {
         // ZEBRA_SESSION
         $link = mysqliConnect();
         self::$xactSession = new Zebra_Session($link, SESSION_HASH, SESSION_LIFETIME_SECONDS);
         //AppUtils::logDebug("Session Created: setLoginValid() with id " . session_id());
         
         $_SESSION['userValid'] = 1;
         $_SESSION['userId'] = $userId;
         $_SESSION['userAccessLevel'] = $accessLevel;
         $_SESSION['userAgent'] = md5($_SERVER['HTTP_USER_AGENT']);
         //AppUtils::logDebug("Session assigned setLoginValid() with id " . session_id());
      }
   }

   /**
    * Get user ID from session
    *
    * @return string User ID
    */
   public static function getUserId()
   {
      if (self::$testMode)
      {
         return self::$testUserId;
      }
      else
      {
         $session = NULL;
          
         // ZEBRA_SESSION
         if (isset(self::$xactSession))
         {   
            $session = self::$xactSession;
         }
         else
         {
            $link = mysqliConnect();
            $session = new Zebra_Session($link, SESSION_HASH, SESSION_LIFETIME_SECONDS);
         }   
         
         if (isset($_SESSION['userId']))
         {
            //AppUtils::logDebug("Session: getUserId() with id " . $_SESSION['userId']);
            self::$xactSession = $session;
            return $_SESSION['userId'];
         }
         else
         {
            //AppUtils::logDebug("Session: getUserId() returning NULL");
            // Remove the invalid session from database
            $session->stop();
            return NULL;
         }
      }
   }

   /**
    * Get session ID
    *
    * @return string Session ID
    */
   public static function getSessionId()
   {
      if (self::$testMode)
      {
         return self::$testSessionId;
      }
      else
      {
         $session = NULL;
          
         // ZEBRA_SESSION
         if (isset(self::$xactSession))
         {
            $session = self::$xactSession;
         }
         else
         {
            $link = mysqliConnect();
            $session = new Zebra_Session($link, SESSION_HASH, SESSION_LIFETIME_SECONDS);
         }

         if (isset($_SESSION['userValid']) && $_SESSION['userValid'])
         {   
            self::$xactSession = $session;
            return session_id();
         }
         else 
         {
            // remove this invalid session
            $session->stop();
            return 'NO-SESSION-ID';            
         }   
      }
   }

   /**
    * Check the session to see if a user is already logged in
    *
    * @return bool login state
    */
   public static function isLoggedIn()
   {
      if (self::$testMode)
      {
         return isset(self::$testUserId);
      }
      else
      {
         // ZEBRA_SESSION
         $session = NULL;
          
         if (isset(self::$xactSession))
         {
            $session = self::$xactSession;
         }
         else
         {
            $link = mysqliConnect();
            $session = new Zebra_Session($link, SESSION_HASH, SESSION_LIFETIME_SECONDS);
         }
          
         
         if (isset($_SESSION['userValid']) && $_SESSION['userValid'] &&
             isset($_SESSION['userAgent']) &&
             ($_SESSION['userAgent'] == md5($_SERVER['HTTP_USER_AGENT'])))
         {
//             AppUtils::logDebug(
//                "Session isLoggedIn() with validity  " . $_SESSION['userValid']);
            self::$xactSession = $session;
            return true;
         }
         else
         {
//            AppUtils::logDebug("Session isLoggedIn() retuning false");
            // remove this invalid session
            $session->stop();
            return false;
         }
      }
   }

   /**
    * logout by destroying the session
    */
   public static function logout()
   {
      if (!self::$testMode)
      {
         // This is handled by cascading delete in database schema
         // This way if the GC deletes the session the event subscriptions will be cleaned up
         // $userId = self::getUserId();
         // if (isset($userId))
         // {
         // // Unsubscribe from all events
         // $pdo = new EventServicePDO();
         // $pdo->unsubscribeForUser($userId);
         // }
         
         /*
          * NO_ZEBRA_SESSION $_SESSION = array(); // destroy all of the session
          * variables session_destroy();
          */
         $link = mysqliConnect();
         $session = new Zebra_Session($link, SESSION_HASH, SESSION_LIFETIME_SECONDS);
         $session->stop();
         self::$xactSession = NULL;
      }
   }
   
   /**
    * purge any sessions left around by users who had no activity
    * and did not log out.
    */
   public static function purgeExpiredSessions()
   {
      try
      {
         $pdo = getPDO();
         $sql = "DELETE FROM session_data WHERE UNIX_TIMESTAMP() > session_expire";
         $stmt = $pdo->prepare($sql);
          $stmt->execute();
      }
      catch (Exception $e)
      {
         throw $e;
      }
      
   }
    
}
