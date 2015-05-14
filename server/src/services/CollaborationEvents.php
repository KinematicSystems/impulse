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
 * ForumEvents
 *
 * Forum Event Notification Constants sent on the forum topic
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumEvent
{
   const DOMAIN = "FORUM";
   
   const CREATE = 'CREATE';
   const UPDATE = 'UPDATE';
   const DELETE = 'DELETE';

   const ENROLLMENT = 'FORUM_ENROLLMENT';
   const CHANGE = 'FORUM_CHANGE';
   const NODE_CHANGE = 'FORUM_NODE_CHANGE';
   const POST_CHANGE = 'FORUM_POST_CHANGE';
}

/**
 * UserEvents
 *
 * Forum Event Notification Constants sent on the user topic
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class UserEvent
{
   const DOMAIN = "USER";
    
   const INVITE = 'USER_INVITE';
   const REJECTED = 'USER_REJECTED';
   const JOINED = 'USER_JOINED'; // Sent when a user creates a forum so that they can subscribe to FORUM events  
   const REMOVED = 'USER_REMOVED';
}
