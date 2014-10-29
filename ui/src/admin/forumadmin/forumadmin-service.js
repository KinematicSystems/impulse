angular.module('services.ForumAdminService',[]).factory('forumAdminService', ['$http', function($http) { 
	var apiUrl = '/impulse/api/';
	var apiSection = 'forums';

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
		getAllForumEnrollment: function(userId) {
			// Array of {forumId, userId, enrollmentStatus, forumName,firstName, lastName, email}
			return runGetRequest(apiSection + "/enrollment/all");
		}
	};
}]);
