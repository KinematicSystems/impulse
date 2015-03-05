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
   const Pending = 'P'; // Invite request pending
   const Accepted = 'A'; // Invitee accepted invite
   const Declined = 'D'; // Invitee declined invite
   const Joined = 'J'; // Invitee is now a member of forum
   const Suspended = 'S'; // Forum membership suspended
   const Left = 'L'; // User Left Forum
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
   const FOLDER_NODE = '#folder';
   public static $ENROLLMENT_CODES = array(
      'I',
      'R',
      'P',
      'A',
      'D',
      'J',
      'S',
      'L'
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
    * Create a new forum and the calls setForumEnrollmentStatus to join the
    * owner to the new forum
    *
    * @param string $forumName
    *           Forum Name
    * @param string $forumDescription
    *           Forum Description
    * @param string $forumOwner
    *           Forum owner User ID
    * @throws Exception
    * @return string new forum ID
    */
   public function createForum($forumName, $forumDescription, $forumOwner)
   {
      try
      {
         $newForum = array();
         $newForum['id'] = AppUtils::guid();
         $newForum['name'] = $forumName;
         $newForum['description'] = $forumDescription;
         $newForum['owner'] = $forumOwner;
         $this->db->forum()->insert($newForum);
         $this->setForumEnrollmentStatus($newForum['id'], $forumOwner, 
            EnrollmentStatus::Joined);
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
    * @param $forumData mixed
    *           Forum object
    * @throws Exception
    */
   public function updateForum($id, $forumData, $updaterId)
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
            $updateData['description'] = $forumData['description'];
            $updateData['owner'] = $updaterId;
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
         $newForumUser['updateUserId'] = AppUtils::getUserId();
         
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
    * Deletes the forum enrollment for the specified forum and user
    *
    * @param string $forumId
    *           Forum ID
    * @param string $userId
    *           Forum Member User ID
    * @throws PDOException
    */
   public function deleteForumEnrollment($forumId, $userId)
   {
      try
      {
         $forumUser = $this->db->forum_user()->where("forumId=? AND userId=?", 
            $forumId, $userId);
         if (isset($forumUser))
            $forumUser->delete();
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all the joined forums for the specified user.
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
         $forums = array();
         $forumUserTable = $this->db->forum_user();
         foreach ($forumUserTable->where("userId=? AND enrollmentStatus = 'J'", $userId) as $forumUser)
         {
            // error_log(print_r($forumUser, true));
            
            $forum = $forumUser->forum;
            $forum['userId'] = $forumUser['userId'];
            $forum['enrollmentStatus'] = $forumUser['enrollmentStatus'];
            $forum['lastUpdated'] = $forumUser['lastUpdated'];
            $forum['updateUserId'] = $forumUser['updateUserId'];
            
            $forums[] = $forum;
         }
         
         return $forums;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }
   /**
    * Returns forum user enrollment info useful for administration
    *
    * @throws PDOException
    * @return array Array of object contatining all fields from forum_user,
    *         user_account, and forumName
    */
   public function getAllForumEnrollment()
   {
      $sql = "SELECT f.name as forumName, fu.*, u.*
      FROM forum_user fu, forum f, user_account u
      WHERE fu.forumId = f.id
      AND u.sysuser is true
      AND fu.userId = u.id
      ORDER BY f.name, u.lastName";
   
      try
      {
         $db = getPDO();
         $stmt = $db->query($sql);
         $results = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
         return $results;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }
   
    
   /**
    * Returns forum user enrollment info for the specified forum
    *
    * @param string $forumId
    *           Forum ID
    * @param boolean $enrolled
    *           specifies if list of users returned are enrolled or not
    * @throws PDOException
    * @return array Array of object contatining all fields from forum_user,
    *         user_account, and forumName, updaterFirstName, updaterLastName
    */
   public function getForumEnrollment($forumId, $enrolled)
   {
      $sql = "";
      
      if ($enrolled)
      {
         $sql = "SELECT f.name as forumName, fu.*, u.*
                  FROM forum_user fu, forum f, user_account u
                  WHERE fu.forumId = :forumId
                  AND u.sysuser is true   
                  AND fu.forumId = f.id
                  AND fu.userId = u.id
                  ORDER BY f.name, u.lastName";
      }
      else
      {
         $sql = "SELECT f.name as forumName, u.id as userId, u.*
                  FROM forum f, user_account u
                  WHERE f.id = :forumId
                  AND u.sysuser is true   
                  AND u.id NOT IN (
                  		SELECT userId 
                  		FROM forum_user 
                  		WHERE forumId = :userForumId) 
                  ORDER BY f.name, u.lastName";
      }
      
      try
      {
         $db = getPDO();
         $stmt = $db->prepare($sql);
         $stmt->bindParam("forumId", $forumId);
         if (!$enrolled)
            $stmt->bindParam("userForumId", $forumId);
         $stmt->execute();
         $results = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
         return $results;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns pending enrollment requests that the specified user is authorized
    * to approve
    * i.e.
    * All the forums that the specified user is already joined that have pending
    * status.
    *
    * @throws PDOException
    * @return array Array of object contatining all fields from forum_user,
    *         user_account, and forumName, updateFirstName, updateLastName
    */
   public function getPendingJoinRequests($userId)
   {
      $sql = "SELECT forum_user.*,forum.name AS forumName
               FROM forum_user, forum
               WHERE enrollmentStatus = 'P'
               AND forum.id = forum_user.forumId
               AND forumId IN (SELECT forumId FROM forum_user 
               WHERE userId = :userId 
               AND enrollmentStatus = 'J'
              )";
      try
      {
         $db = getPDO();
         $stmt = $db->prepare($sql);
         $stmt->bindParam("userId", $userId);
         $stmt->execute();
         $results = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
         return $results;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns pending invitations for the specified user
    * 
    * @param string userId
    *           User ID
    * @throws PDOException
    * @return array Array of object contatining all fields from forum_user,
    *         user_account, and forumName, updateFirstName, updateLastName
    */
   public function getInvitations($userId)
   {
      $sql = "SELECT forum_user.*,forum.name AS forumName
               FROM forum_user, forum
               WHERE enrollmentStatus = 'I'
               AND forum_user.userId = :userId
               AND forum_user.forumId = forum.id";
      try
      {
         $db = getPDO();
         $stmt = $db->prepare($sql);
         $stmt->bindParam("userId", $userId);
         $stmt->execute();
         $results = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
         return $results;
      }
      catch (PDOException $e)
      {
         throw $e;
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
            $this->db->forum_file_node()
               ->where("parentId=?", $parentId)
               ->order('contentType, name'));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns a single forum file node for the specified id
    *
    * @param string $nodeId
    *           Forum File Node ID
    * @throws PDOException
    * @return mixed orum File Node object
    */
   public function getFileNode($nodeId)
   {
      try
      {
         $node = $this->db->forum_file_node()
            ->where("id=?", $nodeId)
            ->fetch();
         return $node;
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
         AppUtils::logDebug(
            "Created file node with ID: {" . $node['id'] . "} and parentId {" .
                $node['parentId'] . "}");
         return $node;
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Rename a forum file node
    *
    * @param string $nodeId
    *           Forum File Node ID
    * @param string $nodeName
    *           New name
    * @throws PDOException
    * @return string the new name
    */
   public function renameFileNode($nodeId, $nodeName)
   {
      try
      {
         $oldNode = $this->db->forum_file_node()->where("id", $nodeId);
         if ($oldNode->fetch())
         {
            // TODO: Not sure why I had to do this put this in array
            // instead of just casting like I did in the user update function.
            $updateData = array();
            $updateData['name'] = $nodeName;
            $result = $oldNode->update($updateData);
            return $nodeName;
         }
         else
         {
            throw new Exception("Forum file node with ID $id does not exist!");
         }
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
            AppUtils::logDebug("Found children for parent {" . $id . "}");
            $this->deleteFileNode($childNode['id']);
         }
         
         $node = $this->db->forum_file_node()->where("id=?", $id);
         $fileNode = $node->fetch();
         if (isset($fileNode) && isset($fileNode['id']))
         {
            $contentType = $fileNode['contentType'];
            // Remove the document from disk if not a folder
            if (isset($contentType) &&
                strcasecmp($contentType, SELF::FOLDER_NODE) != 0)
            {
               unlink(FORUM_UPLOAD_DIR . $fileNode['id']);
            }
            
            AppUtils::logDebug(
               "Deleting node with ID: {" . $fileNode['id'] . "} and parentId {" .
                   $fileNode['parentId'] . "}");
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




