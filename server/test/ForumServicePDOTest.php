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
   private $pdo;
   const USER_ID = 'forumTestGuy';
   const USER_ID2 = 'forumTestGuy2';
    
   /**
    * Prepares the environment before running a test.
    */
   protected function setUp()
   {
      parent::setUp();
      $this->pdo = new ForumServicePDO();
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
      $newUser['id'] = SELF::USER_ID2; 
      $retUser = $this->userPDO->create($newUser);
      PHPUnit_Framework_Assert::assertEquals($retUser['id'], SELF::USER_ID2);
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

      $this->userPDO->delete(SELF::USER_ID2);
      $retUser = $this->userPDO->get(SELF::USER_ID2);
      PHPUnit_Framework_Assert::assertNull($retUser);
   }

   /**
    * One Test because it needs to run in order
    */
   public function testAll()
   {
      PHPUnit_Framework_Assert::assertNotNull($this->pdo);
      
      // *
      // * Forum CRUD Tests
      // *
      
      // Create a Forum
      $forumId = $this->pdo->createForum('TestForum.1', 
         'TestForum.1 Description', SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($forumId);
      
      // Get Forum
      $testForum = $this->pdo->getForum($forumId);
      PHPUnit_Framework_Assert::assertNotNull($testForum);
      PHPUnit_Framework_Assert::assertEquals($testForum['name'], 'TestForum.1');
      PHPUnit_Framework_Assert::assertEquals($testForum['description'],  'TestForum.1 Description');
      PHPUnit_Framework_Assert::assertEquals($testForum['id'], $forumId);
      
      // Update Forum
      $testForum['name'] = 'TestForum.2';
      $testForum['description'] = 'TestForum.2 Description';
      $this->pdo->updateForum($forumId, $testForum, SELF::USER_ID);
      $testForum = $this->pdo->getForum($forumId);
      PHPUnit_Framework_Assert::assertNotNull($testForum);
      PHPUnit_Framework_Assert::assertEquals($testForum['name'], 'TestForum.2');
      PHPUnit_Framework_Assert::assertEquals($testForum['description'], 'TestForum.2 Description');
      
      // Get All Forums
      $forums = $this->pdo->getAllForums();
      PHPUnit_Framework_Assert::assertGreaterThan(0, count($forums));
      PHPUnit_Framework_Assert::assertNotNull($forums[0]['name']);
      
      // Delete Test
      $this->pdo->deleteForum($forumId);
      $testForum = $this->pdo->getForum($forumId);
      PHPUnit_Framework_Assert::assertNull($testForum);
      
      // Create a Forum for future tests
      $forumId = $this->pdo->createForum('TestForum.1', 
         'TestForum.1 Description', SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($forumId);
      
      // *
      // * Forum User Tests
      // *
      
      // Invite User to Forum
      $this->pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID, 
         EnrollmentStatus::Invited);
      $enrollmentStatus = $this->pdo->getForumEnrollmentStatus($forumId, 
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Invited, 
         $enrollmentStatus);
      
      $forums = $this->pdo->getInvitations(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(1,count($forums));
      
      // Enroll User in Forum
      $this->pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID, 
         EnrollmentStatus::Joined);
      $enrollmentStatus = $this->pdo->getForumEnrollmentStatus($forumId, 
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Joined, 
         $enrollmentStatus);
      
      // All Forums for User
      $forums = $this->pdo->getForumsForUser(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(1, count($forums));
      PHPUnit_Framework_Assert::assertNotNull($forums[0]['name']);
      
      // All Enrolled Users for a Forum
      $enrollment = $this->pdo->getForumEnrollment($forumId, true);
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
      $enrollment = $this->pdo->getForumEnrollment($forumId, false);
      PHPUnit_Framework_Assert::assertGreaterThan(0, count($enrollment));
      
      // All Enrolled Users for All Forums (admin function)
      $enrollment = $this->pdo->getAllForumEnrollment();
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
      $this->pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID, 
         EnrollmentStatus::Joined);
      $enrollmentStatus = $this->pdo->getForumEnrollmentStatus($forumId, 
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Joined,$enrollmentStatus); 
         

      // Test Pending Join Request List
      $this->pdo->setForumEnrollmentStatus($forumId, SELF::USER_ID2,
         EnrollmentStatus::Pending);
      $enrollmentStatus = $this->pdo->getForumEnrollmentStatus($forumId,
         SELF::USER_ID2);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Pending,$enrollmentStatus);
      $pendingList = $this->pdo->getPendingJoinRequests(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($pendingList);
      PHPUnit_Framework_Assert::assertEquals(1, count($pendingList));
      
      // Delete Enrollment for User in Forum
      $this->pdo->deleteForumEnrollment($forumId, SELF::USER_ID);
      $enrollmentStatus = $this->pdo->getForumEnrollmentStatus($forumId,
         SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNull($enrollmentStatus);
      $this->pdo->deleteForumEnrollment($forumId, SELF::USER_ID2);
      
      // *
      // * Forum File System Tests
      // *
      $nodeCount = 0;
      
      // Create a folder in the root of the test forum
      $newFolder = array(
         'id' => '',
         'forumId' => $forumId,
         'parentId' => $forumId,
         'name' => 'TestFolder.X',
         'contentType' => ForumServicePDO::FOLDER_NODE
      );
      
      $rootNode = $this->pdo->createFileNode($newFolder);
      $nodes = $this->pdo->getFileNodes($forumId);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
      PHPUnit_Framework_Assert::assertEquals($nodes[0]['name'], 'TestFolder.X');
      
      // Rename node
      $newName = $this->pdo->renameFileNode($rootNode['id'], 'TestFolder.1');
      $nodes = $this->pdo->getFileNodes($forumId);
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
      $childNode = $this->pdo->createFileNode($childNode);
      
      $nodes = $this->pdo->getFileNodes($rootNode['id']);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
      PHPUnit_Framework_Assert::assertNotNull($nodes[0]['name']);
      PHPUnit_Framework_Assert::assertEquals('Child.1', $nodes[0]['name']);
      
      // Delete child folder
      $this->pdo->deleteFileNode($childNode['id']);
      $nodes = $this->pdo->getFileNodes($rootNode['id']);
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
      $headNode = $this->pdo->createFileNode($newFolder);
      AppUtils::logDebug("\n\n\n\nHead Node: " . $headNode['id']);
      
      $newFolder['id'] = '';
      $newFolder['name'] = 'Sibling1';
      $siblingNode = $this->pdo->createFileNode($newFolder);
      AppUtils::logDebug("Sibling Node: " . $siblingNode['id']);
      
      $parentId = $headNode['id'];
      for ($i = 1; $i <= $depth; $i ++)
      {
         $newFolder['parentId'] = $parentId;
         $newFolder['id'] = '';
         $newFolder['name'] = 'Recursive' . $i;
         $newNode = $this->pdo->createFileNode($newFolder);
         AppUtils::logDebug("Child Node: " . $newNode['id']);
         $parentId = $newNode['id'];
      }
      
      AppUtils::logDebug("Deleting");
      $this->pdo->deleteFileNode($headNode['id']);
      $nodes = $this->pdo->getFileNodes($headNode['id']);
      PHPUnit_Framework_Assert::assertEquals(0, count($nodes));
      
      // Ensure that deletion of a folder does not delete it's peers
      $nodes = $this->pdo->getFileNodes($rootNode['id']);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
      PHPUnit_Framework_Assert::assertEquals($nodes[0]['id'], 
         $siblingNode['id']);
      
      AppUtils::logDebug("Clean Up\n\n\n");
      // Clean up = delete all the nodes created during tests
      $this->pdo->deleteFileNode($forumId);
      $nodes = $this->pdo->getFileNodes($forumId);
      PHPUnit_Framework_Assert::assertEquals(0, count($nodes));
      
      
      // *
      // * Clean up test data
      // *
      
      // Delete Test Forum
      $this->pdo->deleteForum($forumId);
      $testForum = $this->pdo->getForum($forumId);
      PHPUnit_Framework_Assert::assertNull($testForum);
   }
}

