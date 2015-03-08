angular.module('services.UserService',[]).factory('userService', ['$http', function($http) { 
	var apiUrl = '../api/';
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
		getAllUsers: function() {
			return runGetRequest('users');
		}, 

		getUser: function(id) {
			return runGetRequest('users/' + id);
		}, 

      getOnlineUsers: function() {
         return runGetRequest('events/subscribers');
      }, 

      createUser: function(user) {
			return $http({
				method: 'POST',
				data: user,
				url: apiUrl + 'users/' + user.id
			}); 
		},

		updateUser: function(user) {
			return $http({
				method: 'PUT',
				data: user,
				url: apiUrl + 'users/' + user.id
			}); 
		},
		
		deleteUser: function(id) {
			return $http({
				method: 'DELETE',
				url: apiUrl + 'users/' + id
			}); 
		},
		
		/* Property Methods */
		getAllProperties: function() {
			return runGetRequest('properties');
		},
		
		getUserProperties: function(userId) {
			return runGetRequest('users/' + userId + '/properties');
		},
		
		assignProperty: function(userId, propId) {
			return $http({
				method: 'PUT',
				data: {},
				url: apiUrl + 'users/' + userId + '/property/' + propId
			}); 
		},
		
		revokeProperty: function(userId, propId) {
			return $http({
				method: 'DELETE',
				url: apiUrl + 'users/' + userId + '/property/' + propId
			}); 
		}

	};
}]);
