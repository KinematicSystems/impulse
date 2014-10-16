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

require_once('../src/config.inc.php');
require_once(__ROOT__.'/src/vendor/NotORM.php');
require_once(__ROOT__.'/src/dbconn.php');
require_once(__ROOT__.'/src/AppUtils.php');
require_once(__ROOT__.'/src/services/UserServicePDO.php');
require_once(__ROOT__.'/src/services/ForumServicePDO.php');

/**
 * ForumService test case.
 */
class ForumServicePDOTest extends PHPUnit_Framework_TestCase
{
	private $pdo;
	const USER_ID = 'forumTestGuy';
	
	/**
    * Prepares the environment before running a test.
    */
   protected function setUp()
   {
      parent::setUp();
      $this->pdo = new ForumServicePDO();
      $this->userPDO = new UserServicePDO();
      
   	// Create a user for forum testing
   	// Not sure how else to do this
   	$newUser = array('id' => SELF::USER_ID,
   	   'firstName' => 'forumUserFirstName',
   	   'lastName' => 'forumUserLastName',
   	   'organization' => 'testOrganization',
   	   'email' => 'forumUser@email.com',
   	   'password' => 'forumUser1234',
   	   'enabled' => 1);
   	
   	// Create User for forum test
   	$retUser = $this->userPDO->create($newUser);
   	PHPUnit_Framework_Assert::assertEquals($retUser['id'], SELF::USER_ID);
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
   }

   /**
    * One Test because it needs to run in order
    */
   public function testAll()
   {
   	PHPUnit_Framework_Assert::assertNotNull($this->pdo);

   	//*
   	//* Forum CRUD Tests
   	//*
   	
   	// Create a Forum
   	$forumId = $this->pdo->createForum('TestForum.1',SELF::USER_ID);
   	PHPUnit_Framework_Assert::assertNotNull($forumId);
   	
   	// Get Forum
   	$testForum = $this->pdo->getForum($forumId);
   	PHPUnit_Framework_Assert::assertNotNull($testForum);
   	PHPUnit_Framework_Assert::assertEquals($testForum['name'], 'TestForum.1');
   	PHPUnit_Framework_Assert::assertEquals($testForum['id'], $forumId);
   	
   	// Update Forum
   	$testForum['name'] = 'TestForum.2';
   	$this->pdo->updateForum($forumId,$testForum);
   	$testForum = $this->pdo->getForum($forumId);
   	PHPUnit_Framework_Assert::assertNotNull($testForum);
   	PHPUnit_Framework_Assert::assertEquals($testForum['name'], 'TestForum.2');
   	
   	// Get All Forums
   	$forums = $this->pdo->getAllForums();
   	PHPUnit_Framework_Assert::assertGreaterThan(0, count($forums));
   	PHPUnit_Framework_Assert::assertNotNull($forums[0]['name']);

   	// Delete Test
   	$this->pdo->deleteForum($forumId);
   	$testForum = $this->pdo->getForum($forumId);
   	PHPUnit_Framework_Assert::assertNull($testForum);

   	// Create a Forum for future tests
   	$forumId = $this->pdo->createForum('TestForum.1',SELF::USER_ID);
   	PHPUnit_Framework_Assert::assertNotNull($forumId);
   	
   	//*
   	//* Forum User Tests
   	//*
   	   	
      // Enroll User in Forum
      $this->pdo->setForumEnrollmentStatus($forumId,SELF::USER_ID,EnrollmentStatus::Invited);
      $enrollmentStatus = $this->pdo->getForumEnrollmentStatus($forumId,SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Invited, $enrollmentStatus);
      
      // All Forums for User
      $forums = $this->pdo->getForumsForUser(SELF::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(1, count($forums));
      PHPUnit_Framework_Assert::assertNotNull($forums[0]['name']);

      // Change Enrollment Status User in Forum
      $this->pdo->setForumEnrollmentStatus($forumId,SELF::USER_ID,EnrollmentStatus::Joined);
      $enrollmentStatus = $this->pdo->getForumEnrollmentStatus($forumId,SELF::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($enrollmentStatus);
      PHPUnit_Framework_Assert::assertEquals(EnrollmentStatus::Joined, $enrollmentStatus);
      
      
      //*
      //* Forum File System Tests
      //*
      $nodeCount = 0;
      
      // Create a folder in the root of the test forum
      $newFolder = array('id' => '',
         'forumId' => $forumId,
         'parentId' => $forumId,
         'name' => 'TestFolder.1',
         'contentType' => ForumServicePDO::FOLDER_NODE
      );
      
      $rootNode = $this->pdo->createFileNode($newFolder);
      $nodes = $this->pdo->getFileNodes($forumId);
      PHPUnit_Framework_Assert::assertEquals(1, count($nodes));
            
      // Add a single child
      $childNode = array('id' => '',
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
      $depth = 50;
      
      $newFolder = array('id' => '',
      		'forumId' => $forumId,
      		'parentId' => $rootNode['id'],
      		'name' => 'Recursive0',
      		'contentType' => ForumServicePDO::FOLDER_NODE
      );
      $headNode = $this->pdo->createFileNode($newFolder);
      $parentId = $headNode['id'];
   	for ($i=1; $i <= $depth; $i++) 
   	{
   		$newFolder['parentId'] = $parentId;	 
  			$newFolder['id'] = '';	 
   		$newFolder['name'] = 'Recursive' . $i;	 
     		$newNode = $this->pdo->createFileNode($newFolder);
     		$parentId = $newNode['id'];
   	} 

    	$this->pdo->deleteFileNode($headNode['id']);
    	$nodes = $this->pdo->getFileNodes($headNode['id']);
    	PHPUnit_Framework_Assert::assertEquals(0, count($nodes));

    	// Clean up = delete all the nodes created during tests
    	$this->pdo->deleteFileNode($forumId);
    	$nodes = $this->pdo->getFileNodes($forumId);
    	PHPUnit_Framework_Assert::assertEquals(0, count($nodes));
    	 
    	//*
    	//* Forum Log Tests
      //*
    	$newLogItem = array('id' => null, // Set by service
    	   'forumId' => $forumId,
    	   'userId' => SELF::USER_ID,
    	   'entryDate' => null, // Set by service
    	   'content' => 'This is a test log item.0'
    	);

    	$forumLogItemId = $this->pdo->createForumLogEntry($newLogItem);
    	PHPUnit_Framework_Assert::assertNotNull($forumLogItemId);
    	PHPUnit_Framework_Assert::assertGreaterThanOrEqual(0,$forumLogItemId);

    	$forumLog = $this->pdo->getForumLog($forumId);
    	PHPUnit_Framework_Assert::assertEquals(1, count($forumLog));
    	 
    	// Add more log items for purge test
    	$logCount = 10;
    	for ($i=1; $i < $logCount; $i++)
    	{
    	   $newLogItem['content'] = 'This is a test log item.' . $i;
    	   $this->pdo->createForumLogEntry($newLogItem);
    	}    
    	$forumLog = $this->pdo->getForumLog($forumId);
    	PHPUnit_Framework_Assert::assertEquals($logCount, count($forumLog));

    	// Purge the log
    	$this->pdo->purgeForumLog($forumId);
    	$forumLog = $this->pdo->getForumLog($forumId);
    	PHPUnit_Framework_Assert::assertEquals(0, count($forumLog));
    	 
   	//*
   	//* Clean up test data
   	//*
   	
   	// Delete Test Forum
   	$this->pdo->deleteForum($forumId);
   	$testForum = $this->pdo->getForum($forumId);
   	PHPUnit_Framework_Assert::assertNull($testForum);
   }
}

