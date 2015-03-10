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
class SessionAuth extends \Slim\Middleware
{
   /**
    *
    * @var string
    */
   protected $realm;

   /**
    * Constructor
    *
    * @param string $realm
    *           The HTTP Authentication realm
    */
   public function __construct($realm = 'Protected Area')
   {
      $this->realm = $realm;
   }

   /**
    * Call
    *
    * This method will check the HTTP request headers for previous
    * authentication. If the request has already authenticated, the next
    * middleware is called. Otherwise, a 401 Authentication Required response is
    * returned to the client.
    */
   public function call()
   {
      $reqURI = $this->app->request()->getResourceUri();
      if ($reqURI == '/login' || $reqURI == '/logout') // Allow the call to
                                                       // login to pass through
      {
         $this->next->call();
      }
      else if (AppUtils::isLoggedIn())
      {
         $this->next->call();
      }
      else // Unauthorized access
      {
         $res = $this->app->response();
         $res->status(401);
      }
   }
}