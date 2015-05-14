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
$app->post('/forum-upload', 'ForumUploadService::upload');

/**
 * ForumUploadService
 *
 * Upload files to folder specified in file config.inc.php 'FORUM_UPLOAD_DIR'
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumUploadService
{

   /**
    * Uploads the file specified via HTTP POST
    * This code is based on the PHP examples
    */
   public static function upload()
   {
      $app = \Slim\Slim::getInstance();
      
      $forumId = $_POST['forumId'];
      $folderId = $_POST['id'];
      $fileName = $_FILES["file"]["name"];
      $tempFileName = $_FILES["file"]["tmp_name"];
      $contentType = $_FILES["file"]["type"];
      
      // Check for errors
      if ($_FILES['file']['error'] > 0)
      {
         $errorMsg = 'Upload Error: ';
         
         // Print a message based upon the error.
         switch ($_FILES['file']['error'])
         {
            case 1:
               $errorMsg = $errorMsg .
                   'The file exceeds the upload_max_filesize setting in php.ini.';
               break;
            case 2:
               $errorMsg = $errorMsg .
                   'The file exceeds the MAX_FILE_SIZE setting in the HTML form.';
               break;
            case 3:
               $errorMsg = $errorMsg . 'The file was only partially uploaded.';
               break;
            case 4:
               $errorMsg = $errorMsg . 'No file was uploaded.';
               break;
            case 6:
               $errorMsg = $errorMsg . 'No temporary folder was available.';
               break;
            case 7:
               $errorMsg = $errorMsg . 'Unable to write to the disk.';
               break;
            case 8:
               $errorMsg = $errorMsg . 'File upload stopped.';
               break;
            default:
               $errorMsg = $errorMsg . 'A system error occurred.';
               break;
         } // End of switch.
         
         AppUtils::sendError(500, "File Upload Error", $errorMsg);
      } // End of error IF.
      else
      {
         try
         {
            $fileNode = array(
               'id' => '',
               'forumId' => $forumId,
               'parentId' => $folderId,
               'name' => $fileName,
               'contentType' => $contentType
            );
            
            $pdo = new ForumServicePDO();
            $fileNode = $pdo->createFileNode((array) $fileNode);
            $fileId = $fileNode['id'];
            
            move_uploaded_file($tempFileName, FORUM_UPLOAD_DIR . $fileId);

            $fileNode['changeType'] = ForumEvent::CREATE;
            
            AppUtils::sendEvent(ForumEvent::DOMAIN, $forumId, ForumEvent::NODE_CHANGE,
            "Node created: " . $fileNode['name'], $fileNode);
                        
            AppUtils::sendResponse($fileNode);
         }
         catch (Exception $e)
         {
            AppUtils::logError($e, __METHOD__);
            AppUtils::sendError($e->getCode(), 
               "Error creating file node for $fileName", $e->getMessage());
         }
      }
      
      // Delete the file if it still exists:
      if (file_exists($_FILES['file']['tmp_name']) &&
          is_file($_FILES['file']['tmp_name']))
      {
         unlink($_FILES['file']['tmp_name']);
      }
   }
}