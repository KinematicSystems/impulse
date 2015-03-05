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
 * ForumService test case. This is dependent on the ForumServicePDO passing tests
 */
class ForumServicePDOTest extends PHPUnit_Framework_TestCase
{
   private $forumPDO;
   private $postPDO;
   private $userPDO;
   private $forumId;
   
   const USER_ID = 'forumTestGuy';
   
   /**
    * Prepares the environment before running a test.
    */
   protected function setUp()
   {
      parent::setUp();
      $this->forumPDO = new ForumServicePDO();
      $this->postPDO = new ForumPostServicePDO();
      $this->userPDO = new UserServicePDO();
      
      AppUtils::setTestMode(true);
      AppUtils::setLoginValid(SELF::USER_ID,"sysuser");
      
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
      $retUser = $this->userPDO->create($newUser);
      PHPUnit_Framework_Assert::assertEquals($retUser['id'], SELF::USER_ID);

      // Create a Forum
      $this->forumId = $this->forumPDO->createForum('TestForum.1', 
         'TestForum.1 Description', SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($this->forumId);
   }

   /**
    * Cleans up the environment after running a test.
    */
   protected function tearDown()
   {
      parent::tearDown();
      
      // Delete User for forum test
      $this->userPDO->delete(SELF::USER_ID);
      $retUser = $this->userPDO->get(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNull($retUser);

      // Delete Test Forum
      $this->forumPDO->deleteForum($this->forumId);
      $testForum = $this->forumPDO->getForum($this->forumId);
      PHPUnit_Framework_Assert::assertNull($testForum);
   }

   /**
    * One Test because it needs to run in order
    */
   public function testAll()
   {
      PHPUnit_Framework_Assert::assertNotNull($this->postPDO);
      
      // *
      // * Forum CRUD Tests
      // *
      
      
      // *
      // * Forum Post Tests
      // *
      $newPostItem = array(
         'id' => null, // Set by service
         'forumId' => $this->forumId,
         'userId' => SELF::USER_ID,
         //'postDate' => null, // Set by service
         'title' => 'TestPost.0',
         'content' => 'This is a test post item.0',
         'contentType' => 'text/plain'
      );
      
      $forumPostItemId = $this->postPDO->createForumPostEntry($newPostItem);
      PHPUnit_Framework_Assert::assertNotNull($forumPostItemId);
      PHPUnit_Framework_Assert::assertGreaterThanOrEqual(0, $forumPostItemId);
      
      $forumPost = $this->postPDO->getForumPost($this->forumId);
      PHPUnit_Framework_Assert::assertEquals(1, count($forumPost));

      // Update the postItem
      $newPostItem['id'] = $forumPostItemId;
      $newPostItem['title'] = 'TestPost.0.A';
      $result = $this->postPDO->updateForumPostEntry($forumPostItemId,$newPostItem);
      PHPUnit_Framework_Assert::assertEquals($result['title'],'TestPost.0.A');
      $result = $this->postPDO->getPosting($this->forumId,$forumPostItemId);
      PHPUnit_Framework_Assert::assertEquals($result['title'],'TestPost.0.A');
      
      // Add more post items for purge test
      $postCount = 10;
      $newPostItem['id'] = null;
      for ($i = 1; $i < $postCount; $i++)
      {
         $newPostItem['title'] = 'TestPost.' . $i;
         $newPostItem['content'] = 'This is a test post item.' . $i;
         // force the postDate for testing purposes
         $newPostItem['postDate'] = '2020-02-0'.$i.' 11:12:13';
         $this->postPDO->createForumPostEntry($newPostItem);
      }
      $forumPost = $this->postPDO->getForumPost($this->forumId);
      PHPUnit_Framework_Assert::assertEquals($postCount, count($forumPost));

      $forumPost = $this->postPDO->getPostSummary($this->forumId);
      PHPUnit_Framework_Assert::assertEquals($forumPost['mostRecentPost']['title'], 'TestPost.9');
//      AppUtils::logDebug($forumPost['mostRecentPost']);

      $forumPost = $this->postPDO->getPostOverviews(SELF::USER_ID);
      $testForumIndex = 0;
      foreach ($forumPost as $theForum)
      {  
         if ($theForum['id'] == $this->forumId)
            break;
             
         ++$testForumIndex;
      }
       
//      AppUtils::logDebug("testForumIndex:".$testForumIndex);
      PHPUnit_Framework_Assert::assertEquals('TestPost.9',$forumPost[$testForumIndex]['mostRecentPost']['title']);
      PHPUnit_Framework_Assert::assertEquals($postCount-1, count($forumPost[$testForumIndex]['otherPosts']));
      
      // Purge the post
      $this->postPDO->purgeForumPost($this->forumId);
      $forumPost = $this->postPDO->getForumPost($this->forumId);
      PHPUnit_Framework_Assert::assertEquals(0, count($forumPost));
   }
}

