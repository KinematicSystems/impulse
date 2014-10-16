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

/**
 * SettingsServicePDO
 *
 * Settings Data Access Object
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class SettingsServicePDO
{
   private $db;

   /**
    * Constructor
    */
   public function __construct()
   {
      $this->db = new NotORM(getPDO());
   }

   /**
    * Returns all the settings.
    *
    * @throws PDOException
    * @return array Array of SystemSetting objects
    */
   public function getAll()
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->system_setting()->order("domain"));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all the settings for the specified domain.
    *
    * @param string $domain
    *           Domain name
    * @throws PDOException
    * @return array Array of SystemSetting objects
    */
   public function getAllDomain($domain)
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->system_setting()->where("domain=?", $domain));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Get the value for the specified domain name and setting key
    *
    * @param string $domain
    *           Domain name
    * @param string $settingKey
    *           Setting key
    * @throws PDOException
    * @return string Value of setting or null
    */
   public function get($domain, $settingKey)
   {
      try
      {
         $setting = $this->db->system_setting()
            ->where("domain=? AND settingKey=?", $domain, $settingKey)
            ->fetch();
         if (!isset($setting) || !($setting))
            return null;
         else
            return $setting;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Create a new setting with specified setting object
    *
    * @param mixed $setting
    *           SystemSetting object
    * @throws PDOException
    */
   public function create($setting)
   {
      try
      {
         $this->db->system_setting()->insert((array) $setting);
         return $setting;
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Update a setting with specified setting object
    *
    * @param mixed $setting
    *           SystemSetting object
    * @throws Exception | PDOException
    */
   public function update($setting)
   {
      $domain = $setting['domain'];
      $key = $setting['settingKey'];
      
      try
      {
         $oldSetting = $this->db->system_setting()->where(
            "domain=? AND settingKey=?", $domain, $key);
         if ($oldSetting->fetch())
         {
            $result = $oldSetting->update((array) $setting);
            return $setting;
         }
         else
         {
            throw new Exception(
               "Setting with domain=$domain and settingKey=$key does not exist!");
         }
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Delete a setting with specified domain name and setting key
    *
    * @param string $domain
    *           Domain name
    * @param string $settingKey
    *           Setting key
    * @throws Exception
    */
   public function delete($domain, $settingKey)
   {
      try
      {
         $setting = $this->db->system_setting()->where(
            "domain=? AND settingKey=?", $domain, $settingKey);
         if (isset($setting))
            $setting->delete();
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all the domain names
    *
    * @return array array of domain names
    */
   public function getDomains()
   {
      try
      {
         // Get all users from system_setting table
         $domains = array();
         foreach ($this->db->system_setting()->select("DISTINCT domain") as $domainRow)
         {
            $domains[] = $domainRow['domain']; // append
         }
         
         return $domains;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }
}





