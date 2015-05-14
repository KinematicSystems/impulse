angular.module('services.SettingsService',[]).factory('settingsService', ['$http', function($http) { 
   var apiUrl = '../api/users/';
   var apiSection = 'settings';
   
   var runGetRequest = function(method) {      
      // Return the promise from the $http service 
      // that calls the admin user API 
      return $http({
         method: 'GET',
         url: apiUrl + method
      }); 
   };
   
   // Return the service object functions
   return {
      getAllSettings: function(userId) {
         return runGetRequest(userId + '/' + apiSection);
      }, 
      getSettingsForDomain: function(userId, domain) {
         return runGetRequest(userId + '/' + apiSection + '/' + domain);
      }, 
      getSetting: function(userId,domain,key) {
         return runGetRequest(userId + '/' + apiSection + '/' + domain + '/' + key);
      }, 
      setSetting: function(userId,domain,key,value) {
         return $http({
            method: 'PUT',
            params: {
               settingValue: value
            },
            url: apiUrl + userId + '/' + apiSection + '/' + domain + '/' + key
         });
      },
      changePassword: function(userId,oldPassword,newPassword) {
         return $http({
            method: 'PUT',
            params: {
               oldPassword: oldPassword,
               newPassword: newPassword
            },
            url: apiUrl + userId + '/password'
         });
      } 
   };
}]);
