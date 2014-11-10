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

/**
 * UserService test case.
 */
class UserServicePDOTest extends PHPUnit_Framework_TestCase
{
   private $pdo;
   const USER_ID = 'testguy';

   /**
    * Prepares the environment before running a test.
    */
   protected function setUp()
   {
      parent::setUp();
      $this->pdo = new UserServicePDO();
      $this->pdo->delete(self::USER_ID);
   }

   /**
    * Cleans up the environment after running a test.
    */
   protected function tearDown()
   {
      parent::tearDown();
   }

   /**
    * One Test because it needs to run in order
    */
   public function testAll()
   {
      PHPUnit_Framework_Assert::assertNotNull($this->pdo);
      
      $newUser = array(
         'id' => self::USER_ID,
         'firstName' => 'testguyFirstName',
         'lastName' => 'testguyLastName',
         'organization' => 'testOrganization',
         'email' => 'testguy@email.com',
         'password' => 'testguy1234!@#$',
         'sysuser' => 1,
         'enabled' => 1
      );
      
      // Create Test
      $retUser = $this->pdo->create($newUser);
      PHPUnit_Framework_Assert::assertEquals($retUser['id'], self::USER_ID);
      
      // Get Test
      $retUser = $this->pdo->get(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals($newUser['id'], $retUser['id']);
      // error_log('Password: '.$retUser['password']);
      
      // Validate Password Test
      $validated = $this->pdo->validateUser(self::USER_ID, 'testguy1234!@#$');
      PHPUnit_Framework_Assert::assertTrue($validated);
      $validated = $this->pdo->validateUser(self::USER_ID, 'testguy1234');
      PHPUnit_Framework_Assert::assertNotTrue($validated);
      
      // Update Test
      $newUser['organization'] = 'updatedTestOrg';
      $newUser['password'] = 'changedPassword';
      $this->pdo->update(self::USER_ID, $newUser);
      $retUser = $this->pdo->get(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals('updatedTestOrg', 
         $retUser['organization']);
      // Validate Updated Password Test
      $validated = $this->pdo->validateUser(self::USER_ID, 'changedPassword');
      PHPUnit_Framework_Assert::assertTrue($validated);
      $validated = $this->pdo->validateUser(self::USER_ID, 'testguy1234');
      PHPUnit_Framework_Assert::assertNotTrue($validated);
      
      // All users
      $allUsers = $this->pdo->getAll();
      // fwrite(STDERR, print_r('Number of users: '.count($allUsers)."\n",
      // TRUE));
      PHPUnit_Framework_Assert::assertGreaterThan(1, count($allUsers));
      PHPUnit_Framework_Assert::assertNotNull($allUsers[0]['id']);
      // fwrite(STDERR, print_r('User id: '.$allUsers[0]['id'], TRUE));
      
      // Delete Test
      $this->pdo->delete(self::USER_ID);
      $retUser = $this->pdo->get(self::USER_ID);
      PHPUnit_Framework_Assert::assertNull($retUser);
      
      // Uses Test Properties from db/test-data.sql
      // These are normally populated as part of system integration
      
      // Property Assignment Tests
      $props = $this->pdo->getAllProperties();
      PHPUnit_Framework_Assert::assertGreaterThan(12, count($props));
      
      $props = $this->pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(0, count($props));
      
      $this->pdo->assignUserProperty(self::USER_ID, 'TestProp.3');
      $props = $this->pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(1, count($props));
      
      $this->pdo->revokeUserProperty(self::USER_ID, 'TestProp.3');
      $props = $this->pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(0, count($props));
      
      $this->pdo->assignUserProperty(self::USER_ID, 'TestProp.1');
      $this->pdo->assignUserProperty(self::USER_ID, 'TestProp.2');
      $this->pdo->assignUserProperty(self::USER_ID, 'TestProp.3');
      $this->pdo->assignUserProperty(self::USER_ID, 'TestProp.4');
      $props = $this->pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(4, count($props));
      
      $this->pdo->revokeUserProperty(self::USER_ID, 'TestProp.1');
      $this->pdo->revokeUserProperty(self::USER_ID, 'TestProp.2');
      $this->pdo->revokeUserProperty(self::USER_ID, 'TestProp.3');
      $this->pdo->revokeUserProperty(self::USER_ID, 'TestProp.4');
      $props = $this->pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(0, count($props));
   }
}

