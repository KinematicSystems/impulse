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
require_once 'UserServicePDO.php';

// Slim Framework Route Mappings
$app->post('/users/:id', 'UserService::create');
$app->get('/users/:id', 'UserService::get');
$app->get('/users', 'UserService::getAll');
$app->put('/users/:id', 'UserService::update');
$app->delete('/users/:id', 'UserService::delete');
$app->get('/users/:id/properties', 'UserService::getUserProperties');
$app->put('/users/:id/property/:propId', 'UserService::assignUserProperty');
$app->delete('/users/:id/property/:propId', 'UserService::revokeUserProperty');
$app->get('/properties', 'UserService::getAllProperties');

/**
 * UserService
 *
 * User Services API
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class UserService
{

   /**
    *
    * @see UserServicePDO::getAll()
    */
   public static function getAll()
   {
      try
      {
         $pdo = new UserServicePDO();
         
         $users = $pdo->getAll();
         
         AppUtils::sendResponse($users);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error getting all users", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see UserServicePDO::get()
    */
   public static function get($id)
   {
      try
      {
         $pdo = new UserServicePDO();
         $user = $pdo->get($id);
         AppUtils::sendResponse($user);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error getting user with ID $id", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see UserServicePDO::create()
    */
   public static function create($id)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         $user = json_decode($body);
         $pdo = new UserServicePDO();
         $pdo->create((array) $user);
         AppUtils::sendResponse($user);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error creating user with ID $id", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see UserServicePDO::update()
    */
   public static function update($id)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new UserServicePDO();
         $user = $pdo->get($id);
         if (isset($user))
         {
            // get and decode JSON request body
            $request = $app->request();
            $body = $request->getBody();
            $userData = json_decode($body);
            
            $pdo->update($id, (array) $userData);
            AppUtils::sendResponse($userData);
         }
         else
         {
            AppUtils::sendResponse(
               array(
                  "success" => false,
                  "message" => "User with ID $id does not exist!"
               ));
         }
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error updating user with ID $id", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see UserServicePDO::delete()
    */
   public static function delete($id)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new UserServicePDO();
         $pdo->delete($id);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error deleting user with ID $id", 
            $e->getMessage());
      }
   }
   
   // **
   // ** User Properties Methods
   // **
   /**
    *
    * @see UserServicePDO::getAllProperties()
    */
   public static function getAllProperties()
   {
      try
      {
         $pdo = new UserServicePDO();
         $props = $pdo->getAllProperties();
         AppUtils::sendResponse($props);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error getting all user properties", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see UserServicePDO::getUserProperties()
    */
   public static function getUserProperties($id)
   {
      try
      {
         $pdo = new UserServicePDO();
         $props = $pdo->getUserProperties($id);
         AppUtils::sendResponse($props);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting properties for user with ID $id", $e->getMessage());
      }
   }

   /**
    *
    * @see UserServicePDO::assignUserProperty()
    */
   public static function assignUserProperty($id, $propId)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new UserServicePDO();
         $pdo->assignUserProperty($id, $propId);
         AppUtils::sendResponse($propId);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error assigning property $propId to user with ID $id", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see UserServicePDO::revokeUserProperty()
    */
   public static function revokeUserProperty($id, $propId)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new UserServicePDO();
         $pdo->revokeUserProperty($id, $propId);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
      }
   }
}

