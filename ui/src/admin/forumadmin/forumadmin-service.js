angular.module('services.ForumAdminService',[]).factory('forumAdminService', ['$http', function($http) { 
	var apiUrl = '../api/';
	var apiSection = 'enrollment';

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
			// Array of object contatining all fields from forum_user, user_account, and forumName
			return runGetRequest(apiSection);
		}
	};
}]);
