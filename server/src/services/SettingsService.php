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
require 'SettingsServicePDO.php';

// Slim Framework Route Mappings
$app->post('/settings', 'SettingsService::create');
$app->get('/settings', 'SettingsService::getAll');
$app->get('/settings/domains', 'SettingsService::getDomains');
$app->get('/settings/:domain', 'SettingsService::getAllDomain');
$app->get('/settings/:domain/:settingKey', 'SettingsService::get');
$app->put('/settings/:domain/:settingKey', 'SettingsService::update');
$app->delete('/settings/:domain/:settingKey', 'SettingsService::delete');
$app->delete('/settings/:domain', 'SettingsService::deleteAllDomain');

/**
 * SettingsService
 *
 * Settings Setvice API
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class SettingsService
{

   /**
    *
    * @see SettingsServicePDO::getAll()
    */
   public static function getAll()
   {
      try
      {
         $pdo = new SettingsServicePDO();
         
         $result = $pdo->getAll();
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error getting all settings", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see SettingsServicePDO::getAllDomain()
    */
   public static function getAllDomain($domain)
   {
      try
      {
         $pdo = new SettingsServicePDO();
         
         $result = $pdo->getAllDomain($domain);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting settings for domain $domain", $e->getMessage());
      }
   }

   /**
    *
    * @see SettingsServicePDO::get()
    */
   public static function get($domain, $settingKey)
   {
      try
      {
         $pdo = new SettingsServicePDO();
         $result = $pdo->get($domain, $settingKey);
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error getting setting for domain $domain/$settingKey", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see SettingsServicePDO::create()
    */
   public static function create()
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         // get and decode JSON request body
         $request = $app->request();
         $body = $request->getBody();
         $setting = json_decode($body);
         $pdo = new SettingsServicePDO();
         $pdo->create((array) $setting);
         AppUtils::sendResponse($setting);
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error creating setting $setting", 
            $e->getMessage());
      }
   }

   /**
    *
    * @see SettingsServicePDO::update()
    */
   public static function update($domain, $settingKey)
   {
      $app = \Slim\Slim::getInstance();
      
      try
      {
         $pdo = new SettingsServicePDO();
         $setting = $pdo->get($domain, $settingKey);
         if (isset($setting))
         {
            // get and decode JSON request body
            $request = $app->request();
            $body = $request->getBody();
            $settingData = json_decode($body);
            
            $pdo->update((array) $settingData);
            AppUtils::sendResponse($settingData);
         }
         else
         {
            AppUtils::sendError(AppUtils::USER_ERROR_CODE, 
               "Setting $domain / $settingKey does not exist!", $e->getMessage());
         }
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error updating setting $domain/$settingKey", $e->getMessage());
      }
   }

   /**
    *
    * @see SettingsServicePDO::delete()
    */
   public static function delete($domain, $settingKey)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new SettingsServicePDO();
         $pdo->delete($domain, $settingKey);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error deleting setting $domain/$settingKey", $e->getMessage());
      }
   }

   /**
    *
    * @see SettingsServicePDO::deleteAllDomain()
    */
   public static function deleteAllDomain($domain)
   {
      $app = \Slim\Slim::getInstance();
      try
      {
         $pdo = new SettingsServicePDO();
         $pdo->deleteAllDomain($domain);
         $app->response()->status(204); // NO DOCUMENT STATUS CODE FOR SUCCESS
      }
      catch (Exception $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), 
            "Error deleting all settings for domain $domain", $e->getMessage());
      }
   }

   /**
    *
    * @see SettingsServicePDO::getDomains()
    */
   public static function getDomains()
   {
      try
      {
         $pdo = new SettingsServicePDO();
         
         $result = $pdo->getDomains();
         AppUtils::sendResponse($result);
      }
      catch (PDOException $e)
      {
         AppUtils::logError($e, __METHOD__);
         AppUtils::sendError($e->getCode(), "Error getting all setting domains", 
            $e->getMessage());
      }
   }
}