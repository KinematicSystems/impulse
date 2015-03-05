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
 * UserServicePDO
 *
 * User Data Access Object
 *
 * @package server
 * @author Matt Grippaldi
 * @since 2.0.0
 */
class UserServicePDO
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
    * Returns all the user rows from the table.
    *
    * Add authroization or any logical checks for secure access to your data
    *
    * @throws PDOException
    * @return array of UserAccount objects
    */
   public function getAll()
   {
      try
      {
         return AppUtils::dbToArray($this->db->user_account());
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns the user with the specified id
    *
    * @param string $id
    *           User ID
    * @throws PDOException
    * @return UserAccount or null
    */
   public function get($id)
   {
      try
      {
         $user = $this->db->user_account()
            ->where("id=?", $id)
            ->fetch();
         
         if (!isset($user) || !$user)
            return null;
         else
            return $user;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns the access level for the specified user
    *
    * @param string $id
    *           User ID
    * @throws PDOException
    * @return Access level string (sysuser || sysreadonly)
    */
   public function getAccess($id)
   {
      try
      {
         $user = $this->db->user_account()
         ->where("id=?", $id)
         ->fetch();
          
         if (!isset($user) || !$user)
            return null;
         else
         {   
            if ($user['sysuser'] == 1)
               return "sysuser";
            else
               return "sysreadonly";
         }   
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }
    
   /**
    * Create a new user with specified user object
    *
    * @param mixed $user
    *           UserAccount object
    * @throws PDOException
    */
   public function create($user)
   {
      try
      {
         // Prevent NotORM mapping error by removing password from array
         $password = $user['password'];
         unset($user['password']);
         $this->db->user_account()->insert((array) $user);
         $this->setCredentials($user['id'], $password);
         return $user;
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Set the password for the specified user
    *
    * @param string $userId
    *           User ID
    * @param string $password
    *           Unencrypted password
    * @throws PDOException
    */
   protected function setCredentials($userId, $password)
   {
      try
      {
         $newCredentials = array(
            'id' => $userId
         );
         
         $credentials = $this->db->user_credentials()
            ->where("id", $userId)
            ->fetch();
         
         if (isset($credentials) && $credentials)
         {
            // Check to see if the password has changed and rehash if necessary
            if (strcasecmp($credentials['password'], $password) != 0)
            {
               $newCredentials['password'] = password_hash($password, 
                  PASSWORD_DEFAULT);
            }
            $result = $credentials->update($newCredentials);
         }
         else
         {
            $newCredentials['password'] = password_hash($password, 
               PASSWORD_DEFAULT);
            
            $this->db->user_credentials()->insert($newCredentials);
         }
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Update the password for the specified user
    *
    * @param string $userId
    *           User ID
    * @param string $password
    *           Unencrypted password
    * @throws PDOException
    */
   public function updatePassword($userId, $oldPassword, $newPassword)
   {
      try
      {
         if ($this->validateUser($userId,$oldPassword))
         {
            $this->setCredentials($userId,$newPassword);
            
         }
         else 
         {
            throw new Exception("Current password is incorrect!");
         } 
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }
    
   /**
    * Update user with specified id and user data
    *
    * @param string $id
    *           User ID
    * @param mixed $userData
    *           UserAccount object
    * @throws Exception
    */
   public function update($id, $userData)
   {
      try
      {
         
         $user = $this->db->user_account()
            ->where("id", $id)
            ->fetch();
         
         if (isset($user) && $user)
         {
            // Prevent NotORM mapping error by removing password from array
            if (isset($userData['password']))
            {
               $password = $userData['password'];
               unset($userData['password']);
               $this->setCredentials($userData['id'], $password);
            }
            
            $result = $user->update((array) $userData);
            return $userData;
         }
         else
         {
            throw new Exception("User with ID $id does not exist!");
         }
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Delete a user with specified id
    *
    * @param string $id
    *           User ID
    * @throws Exception
    */
   public function delete($id)
   {
      try
      {
         $user = $this->db->user_account[$id];
         if (isset($user))
            $user->delete();
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }
   
   // **
   // ** User Properties Methods
   // **
   /**
    * Returns all properties
    *
    * @throws PDOException
    * @return array array of Properties objects
    */
   public function getAllProperties()
   {
      try
      {
         return AppUtils::dbToArray($this->db->properties());
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all properties for the specified user
    *
    * @param string $userId
    *           User ID
    * @throws PDOException
    * @return array array of Properties objects
    */
   public function getUserProperties($userId)
   {
      try
      {
         // Get all users from user_account table
         $userProps = array();
         foreach ($this->db->user_properties()->where("userId", $userId) as $property)
         {
            $userProps[] = $property["propertyId"]; // append
         }
         
         return $userProps;
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Assigns a property to a user
    *
    * @param string $userId
    *           User ID
    * @param string $propId
    *           Property ID
    * @throws PDOException
    * @return array array of Properties objects
    */
   public function assignUserProperty($userId, $propId)
   {
      try
      {
         $userProp = array();
         $userProp['userId'] = $userId;
         $userProp['propertyId'] = $propId;
         $this->db->user_properties()->insert($userProp);
         return $userProp;
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Revokes a property that was assigned to a user
    *
    * @param string $userId
    *           User ID
    * @param string $propId
    *           Property ID
    * @throws Exception
    */
   public function revokeUserProperty($userId, $propId)
   {
      try
      {
         // Had NotORM issues with this, not sure why so just used PDO
         $pdo = getPDO();
         $sql = "DELETE FROM user_properties WHERE `userId` = :userId AND `propertyId` = :propId";
         $stmt = $pdo->prepare($sql);
         $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
         $stmt->bindParam(':propId', $propId, PDO::PARAM_STR);
         $stmt->execute();
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }
   
   // **
   // ** User Settings Methods
   // **
   /**
    * Returns all user settings for the specified user
    *
    * @param string $userId
    *           User ID
    * @throws PDOException
    * @return array array of UserSetting objects
    */
   public function getAllUserSettings($userId)
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->user_settings()
               ->where("userId=?", $userId)
               ->order("domain"));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns all user settings for the specified user and domain
    *
    * @param string $userId
    *           User ID
    * @param string $domain
    *           Specific domain of settings i.e. workspace or the settings for a
    *           specific module
    * @throws PDOException
    * @return array array of UserSetting objects
    */
   public function getUserSettingsForDomain($userId, $domain)
   {
      try
      {
         return AppUtils::dbToArray(
            $this->db->user_settings()->where("userId=? AND domain=?", $userId, 
               $domain));
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }

   /**
    * Returns the value of the setting with the specified parameters
    *
    * @param string $userId
    *           User ID
    * @param string $domain
    *           Specific domain of settings i.e. workspace or the settings for a
    *           specific module
    * @param string $settingKey
    *           The key of the setting to be stored
    * @throws PDOException
    * @return string Value of setting
    */
   public function getUserSetting($userId, $domain, $settingKey)
   {
      try
      {
         $setting = $this->db->user_settings()
            ->where("userId=? AND domain=? AND settingKey=?", $userId, $domain, $settingKey)
            ->fetch();
         
         if (!isset($setting) || !$setting)
            return null;
         else
            return $setting['value'];
      }
      catch (PDOException $e)
      
      {
         throw $e;
      }
   }

   /**
    * Stores a user setting
    *
    * @param string $userId
    *           User ID
    * @param string $domain
    *           Specific domain of settings i.e. workspace or the settings for a
    *           specific module
    * @param string $settingKey
    *           The key of the setting to be stored
    * @param string $settingValue
    *           The value of the setting to be stored
    * @throws PDOException
    * @return mixed The user setting
    */
   public function setUserSetting($userId, $domain, $settingKey, $settingValue)
   {
      try
      {
         $userSetting = array();
         $userSetting['userId'] = $userId;
         $userSetting['domain'] = $domain;
         $userSetting['settingKey'] = $settingKey;
         $userSetting['value'] = $settingValue;
         $this->db->user_settings()->insert_update($userSetting,$userSetting,$userSetting);
         return $userSetting;
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }

   /**
    * Deletes a user setting domain
    *
    * @param string $userId
    *           User ID
    * @param string $domain
    *           Specific domain of settings i.e. workspace or the settings for a
    *           specific module
    * @throws PDOException
    * @return array array of Properties objects
    */
   public function deleteUserSettingsForDomain($userId, $domain)
   {
      try
      {
         // Had NotORM issues with this, not sure why so just used PDO (mattg)
         $pdo = getPDO();
         $sql = "DELETE FROM user_settings WHERE `userId` = :userId AND `domain` = :domain";
         $stmt = $pdo->prepare($sql);
         $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
         $stmt->bindParam(':domain', $domain, PDO::PARAM_STR);
         $stmt->execute();
      }
      catch (Exception $e)
      {
         throw $e;
      }
   }
   
   // **
   // ** User Validation Methods
   // **
   
   /**
    * Validate the user against the encrypted password in the database
    *
    * @param string $userId
    *           User ID
    * @param string $password
    *           password
    * @return boolean
    */
   public function validateUser($userId, $password)
   {
      try
      {
         $credentials = $this->db->user_credentials()
            ->where("id=?", $userId)
            ->fetch();
         
         if (!isset($credentials) || !$credentials)
         {
            return false;
         }
         else
         {
            return (password_verify($password, $credentials['password']));
         }
      }
      catch (PDOException $e)
      {
         throw $e;
      }
   }
}



