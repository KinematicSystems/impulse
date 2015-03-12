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

/**
 * ForumService test case.
 */
class ForumServicePDOTest extends PHPUnit_Framework_TestCase
{
   private static $pdo;
   private static $userPDO;
   const USER_ID = 'forumTestGuy';
   const USER_ID2 = 'forumTestGuy2';

   /**
    * Prepares the environment before running a test.
    */
   public static function setUpBeforeClass()
   {
      self::$pdo = new ForumServicePDO();
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
      $newUser['id'] = SELF::USER_ID2;
      $retUser = self::$userPDO->create($newUser);
      PHPUnit_Framework_Assert::assertEquals($retUser['id'], SELF::USER_ID2);
   }

   /**
    * Cleans up the environment after running a test.
    */
   public static function tearDownAfterClass()
   {
      // Delete User for forum test
      self::$userPDO->delete(SELF::USER_ID);
      $retUser = self::$userPDO->get(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNull($retUser);
      
      self::$userPDO->delete(SELF::USER_ID2);
      $retUser = self::$userPDO->get(SELF::USER_ID2);
      PHPUnit_Framework_Assert::assertNull($retUser);
   }

   public function testCreateForum()
   {
      PHPUnit_Framework_Assert::assertNotNull(self::$pdo);
      // Create a Forum
      $forumId = self::$pdo->createForum('TestForum.1',
         'TestForum.1 Description', SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($forumId);
      return $forumId;
   }

   /**
    * @depends testCreateForum
    */
   public function testGetForum($forumId)
   {
      $testForum = self::$pdo->getForum($forumId);
      PHPUnit_Framework_Assert::assertNotNull($testForum);
      PHPUnit_Framework_Assert::assertEquals($testForum['name'], 'TestForum.1');
      PHPUnit_Framework_Assert::assertEquals($testForum['description'],
      'TestForum.1 Description');
      PHPUnit_Framework_Assert::assertEquals($testForum['id'], $forumId);
      return $testForum;
   }   

   /**
    * @depends testCreateForum
    */
   public function testGetAllForums($forumId)
   {
      $forums = self::$pdo->getAllForums();
      PHPUnit_Framework_Assert::assertGreaterThan(0, count($forums));
      PHPUnit_Framework_Assert::assertNotNull($forums[0]['name']);
   }
    
   /**
    * @depends testGetForum
    */
   public function testUpdateForum($testForum)
   {
      $testForum['name'] = 'TestForum.2';
      $testForum['description'] = 'TestForum.2 Description';
      self::$pdo->updateForum($testForum['id'], $testForum, SELF::USER_ID);
      $testForum = self::$pdo->getForum($testForum['id']);
      PHPUnit_Framework_Assert::assertNotNull($testForum);
      PHPUnit_Framework_Assert::assertEquals($testForum['name'], 'TestForum.2');
      PHPUnit_Framework_Assert::assertEquals($testForum['description'], 
         'TestForum.2 Description');
   }


   /**
    * @depends testCreateForum
    */
   public function testEnrollment($forumId)
   {
      // Invite User to Forum
      self::$pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID,
         EnrollmentStatus::Invited);
      $enrollmentStatus = self::$pdo->getForumEnrollmentStatus($forumId,
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Invited,
      $enrollmentStatus);
      
      $forums = self::$pdo->getInvitations(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(1, count($forums));
      
      // Enroll User in Forum
      self::$pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID,
         EnrollmentStatus::Joined);
      $enrollmentStatus = self::$pdo->getForumEnrollmentStatus($forumId,
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Joined,
      $enrollmentStatus);
      
      // All Forums for User
      $forums = self::$pdo->getForumsForUser(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(1, count($forums));
      PHPUnit_Framework_Assert::assertNotNull($forums[0]['name']);
      
      // All Enrolled Users for a Forum
      $enrollment = self::$pdo->getForumEnrollment($forumId, true);
      PHPUnit_Framework_Assert::assertGreaterThan(0, count($enrollment));
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['forumId']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['userId']);
      PHPUnit_Framework_Assert::assertNotNull(
      $enrollment[0]['enrollmentStatus']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['forumName']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['firstName']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['lastName']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['email']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['lastUpdated']);
      
      // All Users NOT Enrolled for a Forum
      $enrollment = self::$pdo->getForumEnrollment($forumId, false);
      PHPUnit_Framework_Assert::assertGreaterThan(0, count($enrollment));
      
      // All Enrolled Users for All Forums (admin function)
      $enrollment = self::$pdo->getAllForumEnrollment();
      PHPUnit_Framework_Assert::assertGreaterThan(0, count($enrollment));
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['forumId']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['userId']);
      PHPUnit_Framework_Assert::assertNotNull(
      $enrollment[0]['enrollmentStatus']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['forumName']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['firstName']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['lastName']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['email']);
      PHPUnit_Framework_Assert::assertNotNull($enrollment[0]['lastUpdated']);
      
      // Change Enrollment Status User in Forum
      self::$pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID,
         EnrollmentStatus::Joined);
      $enrollmentStatus = self::$pdo->getForumEnrollmentStatus($forumId,
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Joined,
      $enrollmentStatus);
      
      // Test Pending Join Request List
      self::$pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID2,
         EnrollmentStatus::Pending);
      $enrollmentStatus = self::$pdo->getForumEnrollmentStatus($forumId,
         SELF::USER_ID2);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Pending,
      $enrollmentStatus);
      $pendingList = self::$pdo->getPendingJoinRequests(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($pendingList);
      PHPUnit_Framework_Assert::assertEquals(1, count($pendingList));
      
      // Delete Enrollment for User in Forum
      self::$pdo->deleteForumEnrollment($forumId, SELF::USER_ID);
      $enrollmentStatus = self::$pdo->getForumEnrollmentStatus($forumId,
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNull($enrollmentStatus);
      self::$pdo->deleteForumEnrollment($forumId, SELF::USER_ID2);
   
      return $forumId;
   }
       
   /**
    * @depends testCreateForum
    */
   public function testForumFiles($forumId)
   {
      $nodeCount = 0;
      
      // Create a folder in the root of the test forum
      $newFolder = array(
         'id' => '',
         'forumId' => $forumId,
         'parentId' => $forumId,
         'name' => 'TestFolder.X',
         'contentType' => ForumServicePDO::FOLDER_NODE
      );
      
      $rootNode = self::$pdo->createFileNode($newFolder);
      $nodes = self::$pdo->getFileNodes($forumId);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
      PHPUnit_Framework_Assert::assertEquals($nodes[0]['name'], 'TestFolder.X');
      
      // Rename node
      $newName = self::$pdo->renameFileNode($rootNode['id'], 'TestFolder.1');
      $nodes = self::$pdo->getFileNodes($forumId);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
      PHPUnit_Framework_Assert::assertNotEquals($nodes[0]['name'], 
         'TestFolder.X');
      PHPUnit_Framework_Assert::assertEquals($nodes[0]['name'], 'TestFolder.1');
      PHPUnit_Framework_Assert::assertEquals($nodes[0]['id'], $rootNode['id']);
      
      // Add a single child
      $childNode = array(
         'id' => '',
         'forumId' => $forumId,
         'parentId' => $rootNode['id'],
         'name' => 'Child.1',
         'contentType' => ForumServicePDO::FOLDER_NODE
      );
      $childNode = self::$pdo->createFileNode($childNode);
      
      $nodes = self::$pdo->getFileNodes($rootNode['id']);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
      PHPUnit_Framework_Assert::assertNotNull($nodes[0]['name']);
      PHPUnit_Framework_Assert::assertEquals('Child.1', $nodes[0]['name']);
      
      // Delete child folder
      self::$pdo->deleteFileNode($childNode['id']);
      $nodes = self::$pdo->getFileNodes($rootNode['id']);
      PHPUnit_Framework_Assert::assertEquals(0, count($nodes));
      
      // Add $depth child nodes for recursion test of deletion
      $depth = 1;
      
      $newFolder = array(
         'id' => '',
         'forumId' => $forumId,
         'parentId' => $rootNode['id'],
         'name' => 'Recursive0',
         'contentType' => ForumServicePDO::FOLDER_NODE
      );
      $headNode = self::$pdo->createFileNode($newFolder);
 //     AppUtils::logDebug("\n\n\n\nHead Node: " . $headNode['id']);
      
      $newFolder['id'] = '';
      $newFolder['name'] = 'Sibling1';
      $siblingNode = self::$pdo->createFileNode($newFolder);
//      AppUtils::logDebug("Sibling Node: " . $siblingNode['id']);
      
      $parentId = $headNode['id'];
      for ($i = 1; $i <= $depth; $i ++)
      {
         $newFolder['parentId'] = $parentId;
         $newFolder['id'] = '';
         $newFolder['name'] = 'Recursive' . $i;
         $newNode = self::$pdo->createFileNode($newFolder);
//         AppUtils::logDebug("Child Node: " . $newNode['id']);
         $parentId = $newNode['id'];
      }
      
//      AppUtils::logDebug("Deleting");
      self::$pdo->deleteFileNode($headNode['id']);
      $nodes = self::$pdo->getFileNodes($headNode['id']);
      PHPUnit_Framework_Assert::assertEquals(0, count($nodes));
      
      // Ensure that deletion of a folder does not delete it's peers
      $nodes = self::$pdo->getFileNodes($rootNode['id']);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
      PHPUnit_Framework_Assert::assertEquals($nodes[0]['id'], 
         $siblingNode['id']);
      
//      AppUtils::logDebug("Clean Up\n\n\n");
      // Clean up = delete all the nodes created during tests
      self::$pdo->deleteFileNode($forumId);
      $nodes = self::$pdo->getFileNodes($forumId);
      PHPUnit_Framework_Assert::assertEquals(0, count($nodes));

      return $forumId;
   }
   
   /**
    * @depends testForumFiles
    */
   public function testDeleteForum($forumId)
   {
      self::$pdo->deleteForum($forumId);
      $testForum = self::$pdo->getForum($forumId);
      PHPUnit_Framework_Assert::assertNull($testForum);
   }
    
}

