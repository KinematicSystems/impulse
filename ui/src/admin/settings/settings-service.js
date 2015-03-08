angular.module('services.SettingsService',[]).factory('settingsService', ['$http', function($http) { 
	var apiUrl = '../api/';
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
		getAll: function() {
			return runGetRequest(apiSection);
		}, 

		getAllDomain: function(domain) {
			return runGetRequest(apiSection + '/' + domain);
		}, 

		get: function(domain, settingKey) {
			return runGetRequest(apiSection + '/' + domain + '/' + settingKey);
		}, 

		getDomains: function(domain, settingKey) {
			return runGetRequest(apiSection + '/domains');
		}, 

		create: function(setting) {
			return $http({
				method: 'POST',
				data: setting,
				url: apiUrl + apiSection
			}); 
		},

		update: function(setting) {
			return $http({
				method: 'PUT',
				data: setting,
				url: apiUrl + apiSection + '/' + setting.domain + '/' + setting.settingKey
			}); 
		},
		
		deleteData: function(domain, settingKey) {
			return $http({
				method: 'DELETE',
				url: apiUrl + apiSection + '/' + domain + '/' + settingKey
			}); 
		} 
	};
}]);
