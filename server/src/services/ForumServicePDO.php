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
 * EnrollmentStatus
 *
 * Enumeration for forum enrollment status
 */
abstract class EnrollmentStatus
{
   const Invited = 'I'; // User sent invite receipt not confirmed
   const Rejected = 'R'; // System rejected invite of user
   const Pending = 'P'; // Invite receipt confirmed
   const Declined = 'D'; // Invitee declined invite
   const Accepted = 'A'; // Invitee accepted invite
   const Joined = 'J'; // Invitee is now a member of forum
}

/**
 * ForumServicePDO
 *
 * Forum Data Access Object
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumServicePDO
{
   const FOLDER_NODE = 'folder';
   public static $ENROLLMENT_CODES = array(
      'I',
      'R',
      'P',
      'D',
      'A',
      'J'
   );
   private $db;

   /**
    * Constructor
    */
   public function __construct()
   {
      // Configure NotORM to use <table_name>Id rather than <table_name>_id
      // when joining table names. The database field names are camelCase
      // so that the code looks nicer after binding IMHO.
      $structure = new NotORM_Structure_Convention($primary = "id",  // id
         $foreign = "%sId"); // $table_id
      
      $this->db = new NotORM(getPDO(), $structure);
   }

   /**
    * Returns all the forums.
    *
    * @throws PDOException
    * @return array Array of Forum objects
    */
   public function getAllForums()
   {
      try
      {
         return AppUtils::dbToArray($this->db->forum());
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns the forum with specified id.
    *
    * @param string $id
    *           Forum ID
    * @throws PDOException
    * @return mixed Forum object or null
    */
   public function getForum($id)
   {
      try
      {
         $forum = $this->db->forum()
            ->where("id=?", $id)
            ->fetch();
         if (!isset($forum) || !$forum)
            return null;
         else
            return $forum;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Create a new forum
    *
    * @param string $forumName
    *           Forum Name
    * @param string $forumOwner
    *           Forum owner User ID
    * @throws Exception
    * @return string new forum ID
    */
   public function createForum($forumName, $forumOwner)
   {
      try
      {
         $newForum = array();
         $newForum['id'] = AppUtils::guid();
         $newForum['name'] = $forumName;
         $newForum['owner'] = $forumOwner;
         $this->db->forum()->insert($newForum);
         return $newForum['id'];
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Update forum with specified id and forum data
    *
    * @param string $id
    *           Forum ID
    * @param $forumData
    *           mixed Forum object
    * @throws Exception
    */
   public function updateForum($id, $forumData)
   {
      try
      {
         $forum = $this->db->forum()->where("id", $id);
         if ($forum->fetch())
         {
            // TODO: Not sure why I had to do this put this in array
            // instead of just casting like I did in the user update function.
            $updateData = array();
            $updateData['id'] = $forumData['id'];
            $updateData['name'] = $forumData['name'];
            $updateData['owner'] = $forumData['owner'];
            $result = $forum->update($updateData);
            return $forumData;
         }
         else
         {
            throw new Exception("Forum with ID $id does not exist!");
         }
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Delete a forum
    *
    * @param string $id
    *           Forum ID
    * @throws Exception
    */
   public function deleteForum($id)
   {
      try
      {
         $forum = $this->db->forum()->where("id=?", $id);
         
         if (isset($forum))
         {
            $forum->delete();
         }
      }
      catch (Exception $e)
      {
         throw $e;
         error_log(
            'ERROR: ' . __METHOD__ . ' line ' . $e->getLine() . ": " .
                $e->getMessage());
      }
   }

   /**
    * Sets the forum enrollment status and will add the record to the
    * forum_user table if necessary.
    *
    * @param string $forumId
    *           Forum ID
    * @param string $userId
    *           Forum Member User ID
    * @param string $enrollmentStatus
    *           Enrollment status code
    * @throws Exception
    */
   public function setForumEnrollmentStatus($forumId, $userId, $enrollmentStatus)
   {
      try
      {
         // Verify enrollment status code is valid
         if (!in_array($enrollmentStatus, ForumServicePDO::$ENROLLMENT_CODES))
         {
            throw new Exception(
               "Invalid enrollment status code [$enrollmentStatus]!");
         }
         
         $newForumUser = array();
         $newForumUser['forumId'] = $forumId;
         $newForumUser['userId'] = $userId;
         $newForumUser['enrollmentStatus'] = $enrollmentStatus;
         
         $forumUser = $this->db->forum_user()->where("forumId=? AND userId=?", 
            $forumId, $userId);
         
         if ($forumUser->fetch()) // Record already exists just update status
         {
            $forumUser->update($newForumUser);
         }
         else // Add new forum user record
         {
            $this->db->forum_user()->insert($newForumUser);
         }
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Returns the forum enrollment status for the specified forum and user
    *
    * @param string $forumId
    *           Forum ID
    * @param string $userId
    *           Forum Member User ID
    * @throws PDOException
    * @return string Enrollment Status Code or null
    */
   public function getForumEnrollmentStatus($forumId, $userId)
   {
      try
      {
         $forumUser = $this->db->forum_user()
            ->where("forumId=? AND userId=?", $forumId, $userId)
            ->fetch();
         if (!isset($forumUser) || !$forumUser)
            return null;
         else
            return $forumUser["enrollmentStatus"];
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all the forums for the specified user.
    *
    * @param string $userId
    *           User ID
    * @throws PDOException
    * @return array Array of Forum objects
    */
   public function getForumsForUser($userId)
   {
      try
      {
         // Get all users from system_setting table
         $forums = array();
         $forumUserTable = $this->db->forum_user();
         foreach ($forumUserTable->where("userId=?", $userId) as $forumUser)
         {
            $forums[] = $forumUser->forum;
         }
         
         return $forums;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all log entries for the specified forum
    *
    * @param string $forumId
    *           Forum ID
    * @throws PDOException
    * @return array Array of Forum Log objects
    */
   public function getForumLog($forumId)
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->forum_log()
               ->where("forumId=?", $forumId)
               ->order('entryDate desc'));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Create a new forum log entry using the specified object values
    *
    * @param mixed $logItem
    *           Forum log item object
    * @throws Exception
    * @return int ID of new log item assigned by server
    */
   public function createForumLogEntry($logItem)
   {
      try
      {
         /*
          * $logItem['entryDate'] is set in table schema `entryDate` timestamp
          * NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
          * Another option would be to use $logItem['entryDate'] = new
          * NotORM_Literal("NOW()")
          */
         $this->db->forum_log()->insert((array) $logItem);
         return $this->db->forum_log()->insert_id();
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Purges the entire log for a forum
    *
    * @param string $forumId
    *           Forum ID
    * @throws Exception
    */
   public function purgeForumLog($forumId)
   {
      try
      {
         $forumLog = $this->db->forum_log()->where("forumId=?", $forumId);
         
         if (isset($forumLog))
         {
            $forumLog->delete();
         }
      }
      catch (Exception $e)
      {
         throw $e;
         error_log(
            'ERROR: ' . __METHOD__ . ' line ' . $e->getLine() . ": " .
                $e->getMessage());
      }
   }

   /**
    * Returns a single level of forum file nodes for the specified parent
    *
    * @param string $parentId
    *           Parent Node ID
    * @throws PDOException
    * @return array Array of Forum File Node objects
    */
   public function getFileNodes($parentId)
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->forum_file_node()->where("parentId=?", $parentId));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Create a new forum file node using the specified object values
    *
    * @param mixed $node
    *           Forum File Node object
    * @throws PDOException
    * @return mixed new Forum File Node object with GUID assigned by server
    */
   public function createFileNode($node)
   {
      try
      {
         $node['id'] = AppUtils::guid();
         $this->db->forum_file_node()->insert((array) $node);
         return $node;
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Delete a forum file node with the specified ID
    *
    * @param string $id
    *           Forum File Node ID
    * @throws Exception
    */
   public function deleteFileNode($id)
   {
      try
      {
         // Get the IDs of all the children
         foreach ($this->db->forum_file_node()->where("parentId=?", $id) as $childNode)
         {
            $this->deleteFileNode($childNode['id']);
         }
         
         $node = $this->db->forum_file_node()->where("id=?", $id);
         
         if (isset($node))
         {
            $fileNode = $node->fetch();
            $contentType = $fileNode['contentType'];
            // Remove the document from disk if not a folder
            if (isset($contentType) &&
                strcasecmp($contentType, SELF::FOLDER_NODE) != 0)
            {
               unlink(FORUM_UPLOAD_DIR . $fileNode['id']);
            }
            
            $node->delete();
         }
      }
      catch (Exception $e)
      {
         throw $e;
         error_log(
            'ERROR: ' . __METHOD__ . ' line ' . $e->getLine() . ": " .
                $e->getMessage());
      }
   }
}




