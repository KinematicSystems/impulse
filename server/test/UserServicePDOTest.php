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
   private static $pdo;
   const USER_ID = 'testguy';
   const SETTINGS_DOMAIN = 'TestSettings';

   /**
    * Prepares the environment before running a test.
    */
   public static function setUpBeforeClass()
   {
      self::$pdo = new UserServicePDO();
   }

   /**
    * Cleans up the environment after running a test.
    */
   public static function tearDownAfterClass()
   {
      
   }

   public function testCreateUser()
   {
      PHPUnit_Framework_Assert::assertNotNull(self::$pdo);
      
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
      $retUser = self::$pdo->create($newUser);
      PHPUnit_Framework_Assert::assertEquals($retUser['id'], self::USER_ID);
      
      return $retUser['id'];
   }

   /**
    * @depends testCreateUser
    */
   public function testGetUser($userId)
   {
      $retUser = self::$pdo->get(self::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($retUser);
      PHPUnit_Framework_Assert::assertEquals($retUser['id'], $userId);
      return $retUser;
   }

   /**
    * @depends testCreateUser
    */
   public function testGetAllUsers()
   {
      $allUsers = self::$pdo->getAll();
      // fwrite(STDERR, print_r('Number of users: '.count($allUsers)."\n",
      // TRUE));
      PHPUnit_Framework_Assert::assertGreaterThan(1, count($allUsers));
      PHPUnit_Framework_Assert::assertNotNull($allUsers[0]['id']);
      // fwrite(STDERR, print_r('User id: '.$allUsers[0]['id'], TRUE));
   }

   /**
    * @depends testGetUser
    */
   public function testValidatePassword($user)
   {
      // Validate Password Test
      $validated = self::$pdo->validateUser($user['id'], 'testguy1234!@#$');
      PHPUnit_Framework_Assert::assertTrue($validated);
      $validated = self::$pdo->validateUser($user['id'], 'testguy1234');
      PHPUnit_Framework_Assert::assertNotTrue($validated);
   }

   /**
    * @depends testGetUser
    */
   public function testUpdateUser($user)
   {
      $newUser = array(
         'id' => $user['id'],
         'firstName' => 'testguyFirstName',
         'lastName' => 'testguyLastName',
         'organization' => 'updatedTestOrg',
         'email' => 'testguy@email.com',
         'password' => 'changedPassword',
         'sysuser' => 1,
         'enabled' => 1
      );
      
      self::$pdo->update(self::USER_ID, $newUser);
      $retUser = self::$pdo->get(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals('updatedTestOrg', 
         $retUser['organization']);
      // Validate Updated Password Test
      $validated = self::$pdo->validateUser(self::USER_ID, 'changedPassword');
      PHPUnit_Framework_Assert::assertTrue($validated);
      $validated = self::$pdo->validateUser(self::USER_ID, 'testguy1234');
      PHPUnit_Framework_Assert::assertNotTrue($validated);
      // Validate Update with no password parameter in user
      unset($newUser['password']);
      $newUser['organization'] = 'testOrganization';
      self::$pdo->update(self::USER_ID, $newUser);
      $retUser = self::$pdo->get(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals('testOrganization', 
         $retUser['organization']);
      $validated = self::$pdo->validateUser(self::USER_ID, 'changedPassword');
      PHPUnit_Framework_Assert::assertTrue($validated);
   }

   /**
    * @depends testCreateUser
    */
   public function testUserProperties($userId)
   {
      // Uses Test Properties from db/test-data.sql
      // These are normally populated as part of system integration
      // Property Assignment Tests
      $props = self::$pdo->getAllProperties();
      PHPUnit_Framework_Assert::assertGreaterThan(12, count($props));
      
      $props = self::$pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(0, count($props));
      
      self::$pdo->assignUserProperty(self::USER_ID, 'TestProp.3');
      $props = self::$pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(1, count($props));
      
      self::$pdo->revokeUserProperty(self::USER_ID, 'TestProp.3');
      $props = self::$pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(0, count($props));
      
      self::$pdo->assignUserProperty(self::USER_ID, 'TestProp.1');
      self::$pdo->assignUserProperty(self::USER_ID, 'TestProp.2');
      self::$pdo->assignUserProperty(self::USER_ID, 'TestProp.3');
      self::$pdo->assignUserProperty(self::USER_ID, 'TestProp.4');
      $props = self::$pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(4, count($props));
      
      self::$pdo->revokeUserProperty(self::USER_ID, 'TestProp.1');
      self::$pdo->revokeUserProperty(self::USER_ID, 'TestProp.2');
      self::$pdo->revokeUserProperty(self::USER_ID, 'TestProp.3');
      self::$pdo->revokeUserProperty(self::USER_ID, 'TestProp.4');
      $props = self::$pdo->getUserProperties(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(0, count($props));
   }

   /**
    * @depends testCreateUser
    */
   public function testUserSettings($userId)
   {
      // User Settings Tests
      $settings = self::$pdo->getUserSettingsForDomain(self::USER_ID, 
         self::SETTINGS_DOMAIN);
      PHPUnit_Framework_Assert::assertEquals(0, count($settings));
      
      self::$pdo->setUserSetting(self::USER_ID, self::SETTINGS_DOMAIN, 
         'TestSetting.1', 'TestValue.1');
      self::$pdo->setUserSetting(self::USER_ID, self::SETTINGS_DOMAIN, 
         'TestSetting.2', 'TestValue.2');
      self::$pdo->setUserSetting(self::USER_ID, self::SETTINGS_DOMAIN, 
         'TestSetting.3', 'TestValue.3');
      self::$pdo->setUserSetting(self::USER_ID, self::SETTINGS_DOMAIN, 
         'TestSetting.4', 'TestValue.4');
      
      $settings = self::$pdo->getAllUserSettings(self::USER_ID);
      PHPUnit_Framework_Assert::assertEquals(4, count($settings));
      
      $settings = self::$pdo->getUserSettingsForDomain(self::USER_ID, 
         self::SETTINGS_DOMAIN);
      PHPUnit_Framework_Assert::assertEquals(4, count($settings));
      
      $settingVal = self::$pdo->getUserSetting(self::USER_ID, 
         self::SETTINGS_DOMAIN, 'TestSetting.4');
      PHPUnit_Framework_Assert::assertEquals('TestValue.4', $settingVal);
      
      self::$pdo->setUserSetting(self::USER_ID, self::SETTINGS_DOMAIN, 
         'TestSetting.4', 'TestUpdate.4');
      $settingVal = self::$pdo->getUserSetting(self::USER_ID, 
         self::SETTINGS_DOMAIN, 'TestSetting.4');
      PHPUnit_Framework_Assert::assertEquals('TestUpdate.4', $settingVal);
      
      self::$pdo->deleteUserSettingsForDomain(self::USER_ID, 
         self::SETTINGS_DOMAIN);
      $settings = self::$pdo->getUserSettingsForDomain(self::USER_ID, 
         self::SETTINGS_DOMAIN);
      PHPUnit_Framework_Assert::assertEquals(0, count($settings));
   }

   /**
    * @depends testUserSettings
    */
   public function testDeleteUser()
   {
      // Delete Test
      self::$pdo->delete(self::USER_ID);
      $retUser = self::$pdo->get(self::USER_ID);
      PHPUnit_Framework_Assert::assertNull($retUser);
   }
}

