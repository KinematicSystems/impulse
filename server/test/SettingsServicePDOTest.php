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
   private static $pdo;

   /**
    * Prepares the environment before running a test.
    */
   public static function setUpBeforeClass()
   {
      self::$pdo = new SettingsServicePDO();
   }

   /**
    * Cleans up the environment after running a test.
    */
   public static function tearDownAfterClass()
   {
   }

   public function testCreateSetting()
   {
     PHPUnit_Framework_Assert::assertNotNull(self::$pdo);
      
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
      
      $ret = self::$pdo->create($newSetting);
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals($ret['value'], 'TestSettingValue');
      
      return $ret;
    }
   
   /**
    * @depends testCreateSetting
    */
   public function testGetSetting($setting)
   {
      // Get One
      $ret = self::$pdo->get($setting['domain'], $setting['settingKey']);
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertNotNull($ret['value']);
      PHPUnit_Framework_Assert::assertEquals($ret['value'], 'TestSettingValue');
   
      return $ret;
   }

   /**
    * @depends testCreateSetting
    */
   public function testGetAllSettings($setting)
   {
      // Get One
      $settings = self::$pdo->getAll();
      PHPUnit_Framework_Assert::assertGreaterThan(0, count($settings));
      PHPUnit_Framework_Assert::assertNotNull($settings[0]['domain']);
   }
    
   /**
    * @depends testGetSetting
    */
   public function testUpdateSetting($setting)
   {
      $newSetting = array(
         'domain' => $setting['domain'],
         'settingKey' => $setting['settingKey'],
         'value' => 'UpdatedSettingValue',
         'type' => 'STRING',
         'parent' => $setting['parent']
      );
      self::$pdo->update($newSetting);
      $ret = self::$pdo->get('TestDomain', 'TestSettingKey');
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertNotNull($ret['value']);
      PHPUnit_Framework_Assert::assertEquals($ret['value'], 
         'UpdatedSettingValue');
   }

   /**
    * @depends testGetSetting
    */
   public function testDeleteSetting($setting)
   {
      // Delete Test
      self::$pdo->delete($setting['domain'], $setting['settingKey']);
      $ret = self::$pdo->get($setting['domain'], $setting['settingKey']);
      PHPUnit_Framework_Assert::assertNull($ret);
   }

   /**
    * @depends testGetAllSettings
    */
   public function testSettingDomains()
   {
     // Create Parent Child Settings
      $parentSetting = array(
         'domain' => "ParentDomain",
         'settingKey' => 'ParentKey',
         'value' => 'ChildDomain',
         'type' => 'text',
         'parent' => null
      );
      $ret = self::$pdo->create($parentSetting);
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals($ret['value'], 'ChildDomain');

      $childSetting = array(
         'domain' => "ChildDomain",
         'type' => 'text',
         'parent' => 'ParentDomain'
      );
      
      $childCount = 10;
      for ($i=1; $i <= $childCount; $i++)
      {
         $childSetting['settingKey'] = 'ChildKey.'.$i;
         $childSetting['value'] = 'ChildValue.'.$i;
         $ret = self::$pdo->create($childSetting);
         PHPUnit_Framework_Assert::assertNotNull($ret);
      }
      
      // All Settings
      $settings = self::$pdo->getAll();
      PHPUnit_Framework_Assert::assertGreaterThan($childCount, count($settings));
      PHPUnit_Framework_Assert::assertNotNull($settings[0]['domain']);
      // fwrite(STDERR, print_r('Number of settings: '.count($settings)."\n",
      // TRUE));
      
      // All Settings For One Domain
      $settings = self::$pdo->getAllDomain('ChildDomain');
      PHPUnit_Framework_Assert::assertEquals($childCount, count($settings));
      PHPUnit_Framework_Assert::assertNotNull($settings[0]['domain']);
      
      // fwrite(STDERR, print_r('Domain for first setting:
      // '.$settings[0]['domain']."\n", TRUE));
      // fwrite(STDERR, print_r('Setting for first setting:
      // '.$settings[0]['setting']."\n", TRUE));
      // fwrite(STDERR, print_r('Value for first setting:
      // '.$settings[0]['value']."\n", TRUE));
      
      // Domain List
      $domains = self::$pdo->getDomains();
      PHPUnit_Framework_Assert::assertGreaterThan(2, count($domains));
      PHPUnit_Framework_Assert::assertNotNull($domains[0]);

      // Delete all settings for specified domain
      self::$pdo->deleteAllDomain('ChildDomain');
      $settings = self::$pdo->getAllDomain('ChildDomain');
      PHPUnit_Framework_Assert::assertEquals(0, count($settings));
      self::$pdo->deleteAllDomain('ParentDomain');
      $settings = self::$pdo->getAllDomain('ParentDomain');
      PHPUnit_Framework_Assert::assertEquals(0, count($settings));
   }
 }

