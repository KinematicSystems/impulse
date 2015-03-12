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
require_once ('../src/config.inc.php');
require_once (__ROOT__ . '/src/vendor/NotORM.php');
require_once (__ROOT__ . '/src/dbconn.php');
require_once (__ROOT__ . '/src/AppUtils.php');
require_once (__ROOT__ . '/src/services/UserServicePDO.php');
require_once (__ROOT__ . '/src/services/ForumServicePDO.php');
require_once (__ROOT__ . '/src/services/ForumPostServicePDO.php');

/**
 * ForumService test case.
 * This is dependent on the ForumServicePDO passing tests
 */
class ForumServicePDOTest extends PHPUnit_Framework_TestCase
{
   private static $forumPDO;
   private static $postPDO;
   private static $userPDO;
   private static $forumId;
   const USER_ID = 'forumTestGuy';
   const POST_COUNT = 10;

   /**
    * Prepares the environment before running a test.
    */
   public static function setUpBeforeClass()
   {
      // fwrite(STDOUT, __METHOD__ . "\n");
      self::$forumPDO = new ForumServicePDO();
      self::$postPDO = new ForumPostServicePDO();
      self::$userPDO = new UserServicePDO();
      
      AppUtils::setTestMode(true);
      AppUtils::setLoginValid(SELF::USER_ID, "sysuser");
      
      // Create a user for forum testing
      // Not sure how else to do this
      $newUser = array(
         'id' => SELF::USER_ID,
         'firstName' => 'forumUserFirstName',
         'lastName' => 'forumUserLastName',
         'organization' => 'testOrganization',
         'email' => 'forumUser@email.com',
         'password' => 'forumUser1234',
         'sysuser' => 1,
         'enabled' => 1
      );
      
      // Create Users for forum test
      $retUser = self::$userPDO->create($newUser);
      PHPUnit_Framework_Assert::assertEquals($retUser['id'], SELF::USER_ID);
      
      // Create a Forum
      self::$forumId = self::$forumPDO->createForum('TestForum.1', 
         'TestForum.1 Description', SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull(self::$forumId);
   }

   /**
    * Cleans up the environment after running a test.
    */
   public static function tearDownAfterClass()
   {
      // fwrite(STDOUT, __METHOD__ . "\n");
      
      // Delete User for forum test
      self::$userPDO->delete(SELF::USER_ID);
      $retUser = self::$userPDO->get(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNull($retUser);
      
      // Delete Test Forum
      self::$forumPDO->deleteForum(self::$forumId);
      $testForum = self::$forumPDO->getForum(self::$forumId);
      PHPUnit_Framework_Assert::assertNull($testForum);
   }

   public function testCreatePost()
   {
      //fwrite(STDOUT, __METHOD__ . "\n");
      PHPUnit_Framework_Assert::assertNotNull(self::$postPDO);
      
      $newPostItem = array(
         'id' => null, // Set by service
         'forumId' => self::$forumId,
         'userId' => SELF::USER_ID,
         // 'postDate' => null, // Set by service
         'title' => 'TestPost.0',
         'content' => 'This is a test post item.0',
         'contentType' => 'text/plain'
      );
      
      $forumPostItemId = self::$postPDO->createForumPostEntry($newPostItem);
      PHPUnit_Framework_Assert::assertNotNull($forumPostItemId);
      PHPUnit_Framework_Assert::assertGreaterThanOrEqual(0, $forumPostItemId);
      $newPostItem['id'] = $forumPostItemId;
      return $newPostItem;
   }

   /**
    * @depends testCreatePost
    */
   public function testGetPost(array $newPostItem)
   {
      $forumPostItemId = $newPostItem['id'];
      $forumPost = self::$postPDO->getForumPost(self::$forumId);
      self::assertEquals(1, count($forumPost));
   }

   /**
    * @depends testCreatePost
    */
   public function testUpdatePost(array $newPostItem)
   {
      // Update the postItem
      $newPostItem['title'] = 'TestPost.0.A';
      $result = self::$postPDO->updateForumPostEntry($newPostItem['id'], 
         $newPostItem);
      PHPUnit_Framework_Assert::assertEquals($result['title'], 'TestPost.0.A');
      $result = self::$postPDO->getPosting(self::$forumId, $newPostItem['id']);
      PHPUnit_Framework_Assert::assertEquals($result['title'], 'TestPost.0.A');
   }

   /**
    * @depends testCreatePost
    */
   public function testMultiplePosts(array $newPostItem)
   {
      //fwrite(STDOUT, __METHOD__ . "\n");
      // Add more post items for purge test
      $newPostItem['id'] = null;
      for ($i = 1; $i < SELF::POST_COUNT; $i ++)
      {
         $newPostItem['title'] = 'TestPost.' . $i;
         $newPostItem['content'] = 'This is a test post item.' . $i;
         // force the postDate for testing purposes
         $newPostItem['postDate'] = '2020-02-0' . $i . ' 11:12:13';
         self::$postPDO->createForumPostEntry($newPostItem);
      }
      $forumPosts = self::$postPDO->getForumPost(self::$forumId);
      PHPUnit_Framework_Assert::assertEquals(SELF::POST_COUNT, count($forumPosts));
   }

   /**
    * @depends testMultiplePosts
    */
   public function testPostSummary()
   {
      $forumPost = self::$postPDO->getPostSummary(self::$forumId);
      PHPUnit_Framework_Assert::assertEquals(
         $forumPost['mostRecentPost']['title'], 'TestPost.9');
      // AppUtils::logDebug($forumPost['mostRecentPost']);
   }

   /**
    * @depends testMultiplePosts
    */
   public function testPostOverviews()
   {
      // AppUtils::logDebug($forumPost['mostRecentPost']);
      $forumPost = self::$postPDO->getPostOverviews(SELF::USER_ID);
      $testForumIndex = 0;
      foreach ($forumPost as $theForum)
      {
         if ($theForum['id'] == self::$forumId)
            break;
         
         $testForumIndex ++;
      }
      
      // AppUtils::logDebug("testForumIndex:".$testForumIndex);
      PHPUnit_Framework_Assert::assertEquals('TestPost.9', 
         $forumPost[$testForumIndex]['mostRecentPost']['title']);
      PHPUnit_Framework_Assert::assertEquals(SELF::POST_COUNT - 1, 
         count($forumPost[$testForumIndex]['otherPosts']));
   }

   /**
    * @depends testPostOverviews
    */
   public function testPurgePosts()
   {
      // Purge the posts
      self::$postPDO->purgeForumPost(self::$forumId);
      $forumPost = self::$postPDO->getForumPost(self::$forumId);
      PHPUnit_Framework_Assert::assertEquals(0, count($forumPost));
   }
}

