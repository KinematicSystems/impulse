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
    * Send an http response with 500 status and an error message and error code
    * formatted as JSON.
    *
    * @param int $code
    *           Error code
    * @param int $message
    *           Error message
    */
   public static function sendError($code, $message)
   {
      $app = \Slim\Slim::getInstance();
      $app->response()->header('Content-Type', 'application/json');
      // echo '{"error":{"text":' . $message . '", "errorCode":'. $status .
      // '}}';
      $app->response()->status(500);
      $msg = array(
         "error" => true,
         "code" => $code,
         "message" => $message
      );
      
      echo json_encode($msg, JSON_NUMERIC_CHECK);
   }

   /**
    * Send an http response with 400 and send the error message
    * back to the client.
    * Not for production use.
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
      $app->response()->status(400);
      echo '{"error":{"text":' . $exception->getMessage() . '}}';
   }

   /**
    * Log a debug message
    * Writing to the PHP error log for now.
    *
    * @param string $message           
    */
   public static function logDebug($message)
   {
      error_log('DEBUG: ' . $message);
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
    * Store validated user information in session
    *
    * @param string $userId           
    */
   public static function setLoginValid($userId)
   {
      session_start();
      session_regenerate_id(); // this is a security measure
      $_SESSION['userValid'] = 1;
      $_SESSION['userId'] = $userId;
      $_SESSION['userAgent'] = md5($_SERVER['HTTP_USER_AGENT']);
   }

   /**
    * Check the session to see if a user is already logged in
    *
    * @return bool login state
    */
   public static function isLoggedIn()
   {
      session_start();
      
      if (isset($_SESSION['userValid']) && $_SESSION['userValid'] &&
          isset($_SESSION['userAgent']) &&
          ($_SESSION['userAgent'] == md5($_SERVER['HTTP_USER_AGENT'])))
      {
         return true;
      }
      else
      {
         return false;
      }
   }

   /**
    * logout by destroying the session
    */
   public static function logout()
   {
      $_SESSION = array(); // destroy all of the session variables
      session_destroy();
   }
}
