angular.module('services.MapService',[]).factory('mapService', ['$http', function($http) { 
	var apiUrl = '../api/';
	var apiSection = 'map';

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
	};
}]);
