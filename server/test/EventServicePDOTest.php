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
require_once (__ROOT__ . '/src/services/EventServicePDO.php');

/**
 * EventService test case.
 */
class EventServicePDOTest extends PHPUnit_Framework_TestCase
{
   private $pdo;
   const USER_ID = 'testguy';
    
   /**
    * Prepares the environment before running a test.
    */
   protected function setUp()
   {
      parent::setUp();
      $this->pdo = new EventServicePDO();
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
      
      // subscribe 
      $this->pdo->subscribe(self::USER_ID, "testTopic1");
      
      // get subscriptions 
      $ret = $this->pdo->getSubscribers('testTopic1');
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals(1,count($ret));
      PHPUnit_Framework_Assert::assertNotNull($ret[0]);
      PHPUnit_Framework_Assert::assertEquals($ret[0], self::USER_ID);

      // create event
      $this->pdo->pushEvent("sourceGuy", "testTopic1", "content for testTopic1");

      // get event queue
      $ret = $this->pdo->popTopicEvents(self::USER_ID, "testTopic1");
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals(1,count($ret));
      PHPUnit_Framework_Assert::assertNotNull($ret[0]['sourceUserId']);
      PHPUnit_Framework_Assert::assertEquals($ret[0]['sourceUserId'], "sourceGuy");

      // event queue should be empty after pop
      $ret = $this->pdo->popTopicEvents(self::USER_ID, "testTopic1");
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals(0,count($ret));
      
      // create another event and then unsubscribe and queue should be empty
      $this->pdo->pushEvent("sourceGuy", "testTopic1", "content for testTopic1.1");
      $this->pdo->pushEvent("sourceGuy", "testTopic1", "content for testTopic1.2");
      $this->pdo->unsubscribe(self::USER_ID, "testTopic1");
      $ret = $this->pdo->popTopicEvents(self::USER_ID, "testTopic1");
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals(0,count($ret));

      // create events on 2 topics and get both 
      $this->pdo->subscribe(self::USER_ID, "testTopic1");
      $this->pdo->subscribe(self::USER_ID, "testTopic2");
      $this->pdo->pushEvent("sourceGuy", "testTopic1", "content for testTopic1.1");
      $this->pdo->pushEvent("sourceGuy", "testTopic2", "content for testTopic2.1");
      $this->pdo->pushEvent("sourceGuy", "testTopic1", "content for testTopic1.2");
      $this->pdo->pushEvent("sourceGuy", "testTopic2", "content for testTopic2.2");
      $ret = $this->pdo->popEvents(self::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals(4,count($ret));
      
      $this->pdo->unsubscribe(self::USER_ID, "testTopic1");
      $this->pdo->unsubscribe(self::USER_ID, "testTopic2");
      $ret = $this->pdo->popEvents(self::USER_ID);
      PHPUnit_Framework_Assert::assertNotNull($ret);
      PHPUnit_Framework_Assert::assertEquals(0,count($ret));
      
   }
}

