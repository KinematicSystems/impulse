/**
 * This service makes the calls to the impulse login API. It hooks the 401
 * errors from the service call with the security interceptor added to $http.
 * The interceptor must be hooked in the config phase of the service module.
 * (mattg)
 */

// loginService server API
angular.module('services.LoginService', [ 'services.ImpulseService' ]).factory(
      'loginService',
      [ '$http', '$window', '$rootScope', 'impulseService', 'LOGIN_EVENTS',
            function($http, $window, $rootScope, impulseService, LOGIN_EVENTS) {
               var apiUrl = '../api/';

               // Return the service object functions
               return {
                  isLoggedIn: function() {
                     var storedUser = $window.sessionStorage.getItem("userInfo");
                     return (storedUser !== undefined && storedUser !== null && storedUser !== "null");
                  },
                  restoreSession: function() {
                     // If the user is still in the session fire a login success
                     var storedUserData = $window.sessionStorage.getItem("userInfo");
                     if (storedUserData !== undefined && storedUserData !== null && storedUserData !== "null")
                     {
                        var storedUser = JSON.parse(storedUserData);
                        impulseService.setCurrentUser(storedUser.userId);
                        impulseService.setCollaborator((storedUser.accessLevel === 'sysuser'));
                        $rootScope.$broadcast(LOGIN_EVENTS.LOGIN_SUCCESS, storedUser);
                     }
                  },
                  login: function(userId, password) {
                     $http({
                        method: 'POST',
                        data: {
                           userId: userId,
                           password: password
                        },
                        url: apiUrl + 'login'
                     }).success(function(data, status) {
                        if (status === 200)
                        {
                           var userInfo = {userId: userId, accessLevel: data};
                           $window.sessionStorage.setItem("userInfo", JSON.stringify(userInfo));
                           impulseService.setCurrentUser(userId);
                           impulseService.setCollaborator((data === 'sysuser'));
                           $rootScope.$broadcast(LOGIN_EVENTS.LOGIN_SUCCESS, userInfo);
                        }
                        else
                        {
                           $window.sessionStorage.removeItem("userInfo");
                           impulseService.setCurrentUser(null);
                           $rootScope.$broadcast(LOGIN_EVENTS.LOGIN_FAILED, "Bad Login!");
                        }
                     }).error(function(data, status) {
                        $window.sessionStorage.removeItem("userInfo");
                        impulseService.setCurrentUser(null);
                        $rootScope.$broadcast(LOGIN_EVENTS.LOGIN_FAILED, data.details); // Error details
                     });
                  },
                  logout: function() {
                     $http({
                        method: 'GET',
                        url: apiUrl + 'logout'
                     }).success(function(data, status) {
                        $window.sessionStorage.removeItem("userInfo");
                        impulseService.setCurrentUser(null);
                        $rootScope.$broadcast(LOGIN_EVENTS.LOGOUT_SUCCESS);
                     });
                  }
               };
            } ])
// securityInterceptor $http interceptor
.factory('securityInterceptor', [ '$q', '$rootScope', 'LOGIN_EVENTS', function($q, $rootScope, LOGIN_EVENTS) {
   return {
      'responseError': function(rejection) {
         if (rejection.status === 401)
         {
            var param = rejection.config;
            $rootScope.$broadcast(LOGIN_EVENTS.NOT_AUTHENTICATED, "Not logged in!");
         }
         return $q.reject(rejection);
      }
   };
} ])
// Add the sercurity intercept   or to the $http service in config phase
.config([ '$httpProvider', function($httpProvider) {
   $httpProvider.interceptors.push('securityInterceptor');
} ]);