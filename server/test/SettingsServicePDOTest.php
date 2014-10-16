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
require_once (__ROOT__ . '/src/services/SettingsServicePDO.php');

/**
 * SettingsService test case.
 */
class SettingsServicePDOTest extends PHPUnit_Framework_TestCase
{
   private $pdo;

   /**
    * Prepares the environment before running a test.
    */
   protected function setUp()
   {
      parent::setUp();
      $this->pdo = new SettingsServicePDO();
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
      
      // Uses Test Settings from db/test-data.sql
      // These are normally populated as part of system integration
      
      // Create
      $newSetting = array(
         'domain' => "TestDomain",
         'settingKey' => 'TestSettingKey',
         'value' => 'TestSettingValue',
         'type' => 'STRING',
         'parent' => 'TestSettingParent'
      );
      
      $ret = $this->pdo->create($newSetting);
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals($ret['value'], 'TestSettingValue');
      
      // Get One
      $ret = $this->pdo->get('TestDomain', 'TestSettingKey');
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertNotNull($ret['value']);
      PHPUnit_Framework_Assert::assertEquals($ret['value'], 'TestSettingValue');
      
      // Update
      $newSetting['value'] = 'UpdatedSettingValue';
      $this->pdo->update($newSetting);
      $ret = $this->pdo->get('TestDomain', 'TestSettingKey');
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertNotNull($ret['value']);
      PHPUnit_Framework_Assert::assertEquals($ret['value'], 
         'UpdatedSettingValue');
      
      // All Settings
      $settings = $this->pdo->getAll();
      PHPUnit_Framework_Assert::assertGreaterThan(6, count($settings));
      PHPUnit_Framework_Assert::assertNotNull($settings[0]['domain']);
      // fwrite(STDERR, print_r('Number of settings: '.count($settings)."\n",
      // TRUE));
      
      // All Settings For One Domain
      $settings = $this->pdo->getAllDomain('TestDomain.1');
      PHPUnit_Framework_Assert::assertEquals(3, count($settings));
      PHPUnit_Framework_Assert::assertNotNull($settings[0]['domain']);
      
      // fwrite(STDERR, print_r('Domain for first setting:
      // '.$settings[0]['domain']."\n", TRUE));
      // fwrite(STDERR, print_r('Setting for first setting:
      // '.$settings[0]['setting']."\n", TRUE));
      // fwrite(STDERR, print_r('Value for first setting:
      // '.$settings[0]['value']."\n", TRUE));
      
      // Domain List
      $domains = $this->pdo->getDomains();
      PHPUnit_Framework_Assert::assertGreaterThan(6, count($domains));
      PHPUnit_Framework_Assert::assertNotNull($domains[0]);
      
      // Delete Test
      $this->pdo->delete('TestDomain', 'TestSettingKey');
      $ret = $this->pdo->get('TestDomain', 'TestSettingKey');
      PHPUnit_Framework_Assert::assertNull($ret);
   }
}

