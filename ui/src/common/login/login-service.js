/**
 * This service makes the calls to the impulse login API.  It hooks the 401 errors from the service call
 * with the security interceptor added to $http.  The interceptor must be hooked in the config phase of the 
 * service module. (mattg)
 */

// loginService server API
angular.module('services.LoginService',['services.ImpulseService']).factory('loginService', ['$http', '$window', '$rootScope','LOGIN_EVENTS',
    function($http, $window, $rootScope, LOGIN_EVENTS) { 
	var apiUrl = '/impulse/api/';
	
	// Return the service object functions
	return {
		currentUser: function() {
			return $window.sessionStorage.getItem("userInfo");
		},
		restoreSession: function() {
			// If the user is still in the session fire a login success
			var storedUser = $window.sessionStorage.getItem("userInfo");
			if (storedUser !== undefined && storedUser !== null && storedUser !== "null")
			{
		       	$rootScope.$broadcast(LOGIN_EVENTS.LOGIN_SUCCESS, storedUser);
			}
		},
		login: function(userId, password) {
			$http({method: 'POST', data: {userId: userId, password: password}, url: apiUrl + 'login'})
			.success(function(data, status) {
				if (status === 200) 
				{	
		           	$rootScope.$broadcast(LOGIN_EVENTS.LOGIN_SUCCESS, userId);
			        $window.sessionStorage.setItem("userInfo", userId);
				}
				else
				{
			        $window.sessionStorage.removeItem("userInfo");
		           	$rootScope.$broadcast(LOGIN_EVENTS.LOGIN_FAILED,"Bad Login!");
				}
			})
			.error(function(data, status) {
		        $window.sessionStorage.removeItem("userInfo");
	           	$rootScope.$broadcast(LOGIN_EVENTS.LOGIN_FAILED,data.details); // Error details
			});
		},
		logout: function() {
			$http({method: 'GET',url: apiUrl + 'logout'})
			.success(function(data, status) {
		        $window.sessionStorage.removeItem("userInfo");
	           	$rootScope.$broadcast(LOGIN_EVENTS.LOGOUT_SUCCESS);
			});
		} 
	};
}])
// securityInterceptor $http interceptor
.factory('securityInterceptor', ['$q', '$rootScope', 'LOGIN_EVENTS', function($q, $rootScope, LOGIN_EVENTS) {
        return {
             'responseError': function (rejection) {
               if (rejection.status === 401) 
               {
            	   var param = rejection.config;
            	   $rootScope.$broadcast(LOGIN_EVENTS.NOT_AUTHENTICATED,"Not logged in!");
               }
               return $q.reject(rejection);
            }
        };
}])
// Add the sercurity intercept   or to the $http service in config phase
.config(['$httpProvider', function($httpProvider) {
    $httpProvider.interceptors.push('securityInterceptor');
}]);