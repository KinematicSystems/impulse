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
 * PostType
 *
 * Enumeration for forum enrollment status
 */
abstract class PostType
{
   const Post = 'post'; // Post entry is a post
}

/**
 * ForumPostServicePDO
 *
 * Forum Posting Data Access Object
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class ForumPostServicePDO
{
   private $db;
   const OVERVIEW_MAXLEN = 512;

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
    * Returns all post entries for the specified forum
    *
    * @param string $forumId
    *           Forum ID
    * @throws PDOException
    * @return array Array of Forum Post objects
    */
   public function getForumPost($forumId)
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->forum_post()
               ->where("forumId=?", $forumId)
               ->order('postDate desc'));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all post entries for the specified forum
    *
    * @throws PDOException
    * @return array Array of Post Template objects
    */
   public function getPostTemplates()
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->post_template()
            ->where("category=?", 'General')
            ->order('name asc'));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }
    
   /**
    * Returns the forum posting with ID $postId
    *
    * @param string $forumId
    *           Forum ID
    * @param string $postId
    *           Posting ID
    * @throws PDOException
    * @return mixed The post object
    */
   public function getPosting($forumId, $postId)
   {
      try
      {
         $posting = $this->db->forum_post()
            ->where("id", $postId)
            ->fetch();
         
         if (!isset($posting) || !$posting)
            return null;
         else
            return $posting;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns the forum posting summary for the specified forum
    * Number of posts, posting day range, summary of most recent post.
    *
    * @param string $forumId
    *           Forum ID
    * @throws PDOException
    * @return mixed The post summary object
    */
   public function getPostSummary($forumId)
   {
      $countSql = "SELECT count(*) AS numPosts, 
	           DATEDIFF(max(postDate),min(postDate))+1 AS numDays
              FROM forum_post
              WHERE forumId = :forumId";
      
      $recentSql = "SELECT * FROM forum_post 
                     WHERE forumId = :forumId 
                     AND postDate = (SELECT MAX(fp2.postDate) 
                                    FROM forum_post fp2 WHERE fp2.forumId = :forumId2)";
      
      try
      {
         $db = getPDO();
         $stmt = $db->prepare($countSql);
         $stmt->bindParam("forumId", $forumId);
         $stmt->execute();
         $counts = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
         
         $stmt2 = $db->prepare($recentSql);
         $stmt2->bindParam("forumId", $forumId);
         $stmt2->bindParam("forumId2", $forumId);
         $stmt2->execute();
         $recent = (array) $stmt2->fetchAll(PDO::FETCH_ASSOC);
         $result = array(
            "numPosts" => $counts[0]['numPosts'],
            "numDays" => $counts[0]['numDays'],
            "mostRecentPost" => $recent[0]
         );
         
         return $result;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns the forum posting overviews that are used on the overview
    * screen of the UI.
    * The key additions to the forum list in the
    * method is that there is an isMember flag added so that it can be
    * determined if
    * join requests links will be displayed. Also forums that do not have any
    * posts will not be returned depending on whether a LEFT JOIN is used.
    *
    * Summary is
    * Number of posts, posting day range, summary of most recent post.
    *
    * @throws PDOException
    * @return mixed The post summary object
    */
   public function getPostOverviews($userId)
   {
      // NOTE: If you want all the the forum not just the ones with posts
      // USE 'LEFT JOIN forum_post p' instead of 'JOIN forum_post p'
      $sql = "SELECT f.*, NOT(ISNULL(u.userId)) as isMember,
      DATEDIFF(max(p.postDate),min(p.postDate))+1 AS numDays,
      count(p.title) as numPosts
      FROM forum f
      LEFT JOIN forum_user u
      ON (f.id = u.forumId AND u.userId = :userId)
      JOIN forum_post p
      ON (f.id = p.forumId)
      GROUP BY f.id";
      
      try
      {
         $db = getPDO();
         $stmt = $db->prepare($sql);
         $stmt->bindParam("userId", $userId);
         $stmt->execute();
         $forums = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);

//          $sql2 = "SELECT * FROM forum_post
//                      WHERE forumId = :forumId
//                      AND postDate = (SELECT MAX(fp2.postDate)
//                      FROM forum_post fp2 WHERE fp2.forumId = :forumId2)";
         $sql2 = "SELECT * FROM forum_post
                     WHERE forumId = :forumId
                     ORDER BY postDate DESC";
         $stmt2 = $db->prepare($sql2);
          
         foreach ($forums as &$forum)
         {
            $isFirstPost = true;
            $forum['otherPosts'] = [];
            $stmt2->bindParam("forumId", $forum['id']);
            $stmt2->execute();
            $posts = (array) $stmt2->fetchAll(PDO::FETCH_ASSOC);
            foreach ($posts as &$post)
            {
               $post['truncated'] = false;
                
               if ($isFirstPost)
               {
                  $isFirstPost = false;
                  if (strlen($post['content']) > SELF::OVERVIEW_MAXLEN)
                  {
                     $post['content'] = substr($post['content'], 0,
                        SELF::OVERVIEW_MAXLEN);
                     $post['content'] = $post['content'] . ' .....';
                     $post['truncated'] = true;
                  }
                  $forum['mostRecentPost'] = $post;
               }   
               else // don't send the content for the other posts lists
               {
                  unset($post['content']);
                  $forum['otherPosts'][] = $post;
               }   
             }
            
/*
            $recentSql = "SELECT * FROM forum_post
                     WHERE forumId = :forumId
                     AND postDate = (SELECT MAX(fp2.postDate)
                     FROM forum_post fp2 WHERE fp2.forumId = :forumId2)";
            
            $stmt2 = $db->prepare($recentSql);
            $stmt2->bindParam("forumId", $forums[$i]['id']);
            $stmt2->bindParam("forumId2", $forums[$i]['id']);
            $stmt2->execute();
            $recent = (array) $stmt2->fetchAll(PDO::FETCH_ASSOC);
            if (isset($recent) && count($recent) > 0)
            {
               $post = $recent[0];
               $post['truncated'] = false;
                
               if (strlen($post['content']) > SELF::OVERVIEW_MAXLEN)
               {
                  $post['content'] = substr($post['content'], 0, 
                     SELF::OVERVIEW_MAXLEN);
                  $post['content'] = $post['content'] . ' .....';
                  $post['truncated'] = true;
               }
               // AppUtils::logDebug($post);
               
               $forums[$i]['mostRecentPost'] = $post;
            }
*/
         }
         
         return $forums;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Create a new forum post entry using the specified object values
    *
    * @param mixed $postItem
    *           Forum post item object
    * @throws Exception
    * @return int ID of new post item assigned by server
    */
   public function createForumPostEntry($postItem)
   {
      try
      {
         unset($postItem['id']); // Let database generate ID
         $this->db->forum_post()->insert((array) $postItem);
         return $this->db->forum_post()->insert_id();
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Update a new forum post entry
    *
    * @param string $id
    *           Post ID
    * @param mixed $postItem
    *           Forum post item object
    * @throws Exception
    * @return int ID of new post item assigned by server
    */
   public function updateForumPostEntry($id, $postItem)
   {
      try
      {
         
         $posting = $this->db->forum_post()
            ->where("id", $id)
            ->fetch();
         
         if (isset($posting) && $posting)
         {
            unset($postItem['postDate']); // Let database update postDate
            $result = $posting->update((array) $postItem);
            return $postItem;
         }
         else
         {
            throw new Exception("Forum posting with ID $id does not exist!");
         }
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Purges the entire post for a forum
    *
    * @param string $forumId
    *           Forum ID
    * @throws Exception
    */
   public function purgeForumPost($forumId)
   {
      try
      {
         $forumPost = $this->db->forum_post()->where("forumId=?", $forumId);
         
         if (isset($forumPost))
         {
            $forumPost->delete();
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




