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

/*
 * Application Includes
 */
require_once 'vendor/Slim/Slim.php';
require_once 'vendor/Slim/Middleware.php';
require_once 'vendor/NotORM.php';
require_once 'vendor/Zebra_Session/Zebra_Session.php';

require_once 'dbconn.php';
require_once 'AppUtils.php';
require_once 'config.inc.php';
require_once 'SessionAuth.php';

/*
 * Register Slim Framework
 */
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

/*
 * REST Services
 */
require_once 'services/AuthenticationService.php';
require_once 'services/CollaborationEvents.php';
require_once 'services/UserService.php';
require_once 'services/ForumAdminService.php';
require_once 'services/ForumFileService.php';
require_once 'services/ForumPostService.php';
require_once 'services/ForumEnrollmentService.php';
require_once 'services/ForumUploadService.php';
require_once 'services/SettingsService.php';
require_once 'services/EventService.php';

/*
 * Add the SessionAuth HTTP interceptor that will ensure that every call is
 * coming from an authenticated user.
 */
$app->add(new \SessionAuth());

/*
 * Run the Slim app. Caution don't close any <?php tags in this application it
 * will cause the response to be sent prematurely.
 */
$app->run();
   



