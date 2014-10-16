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

// Slim Framework Route Mappings
$app->post('/login', 'AuthenticationService::login');
$app->get('/logout', 'AuthenticationService::logout');
$app->get('/login/:userId', 'AuthenticationService::ping');

/**
 * AuthenticationService
 *
 * This class authenticates the user against the user_account table.
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class AuthenticationService
{

   /**
    * Utility function for testing login
    *
    * @param string $userId           
    */
   public static function ping($userId)
   {
      $app = \Slim\Slim::getInstance();
      AppUtils::sendResponse("Hello " . $userId);
   }

   /**
    * Login the user with credentials past in POST
    */
   public static function login()
   {
      $app = \Slim\Slim::getInstance();
      AppUtils::logout();
      
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $response = $app->response();
         $body = $request->getBody();
         $login = (array) json_decode($body);
         $loginOK = false;
         
         if (isset($login['userId']) && isset($login['password']))
         {
            $userService = new UserServicePDO();
            if ($userService->validateUser($login['userId'], $login['password']))
            {
               AppUtils::setLoginValid($login['userId']);
               $loginOK = true;
            }
         }
         
         if (!$loginOK)
         {
            $response->status(401);
         }
         else
            AppUtils::sendResponse($login['userId']);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
      }
   }

   /**
    * Logs out current user and sends a response of Logged Out
    * until something else more useful should be returned.
    */
   public static function logout()
   {
      AppUtils::logout();
      AppUtils::sendResponse('Logged Out');
   }
}

          