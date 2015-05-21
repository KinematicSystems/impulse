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
define('LIVE_MODE', FALSE); // Flag variable for site status
define('FORUM_UPLOAD_DIR', '/src/www/impulse-uploads/');
define('DB_NAME', 'impulse');
define('DB_USER', 'impulse');
define('DB_PASSWORD', 'csptech');
define('DB_HOST', '127.0.0.1');
define('SESSION_HASH', 'iMpulSe321#@');
define('SESSION_LIFETIME_SECONDS', 1200); // 20 minutes
define('STOMP_EVENTING', true); // true for STOMP false for IMPULSE eventing services
define('ACCESS_LOG_FILE', '/src/www/impulse_access_log.txt'); // path and filename for user access log file

define('__ROOT__', dirname(dirname(__FILE__)));

