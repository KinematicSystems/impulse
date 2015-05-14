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

               var clearUser = function(eventID, msg) {
                  $window.sessionStorage.removeItem("userInfo");
                  impulseService.setCurrentUserId(null);
                  $rootScope.$broadcast(eventID, msg);
               };

               var setUser = function(userInfo) {
                  $window.sessionStorage.setItem("userInfo", JSON.stringify(userInfo));
                  impulseService.setCurrentUserId(userInfo.userId);
                  impulseService.setCollaborator((userInfo.accessLevel === 'sysuser'));
                  $rootScope.$broadcast(LOGIN_EVENTS.LOGIN_SUCCESS, userInfo);
               };

               // Return the service object functions
               return {
                   restoreSession: function() {
                     // If the user is still in the session fire a login success
                     var storedUserData = $window.sessionStorage.getItem("userInfo");
                     if (storedUserData !== undefined && storedUserData !== null && storedUserData !== "null")
                     {
                        var storedUser = JSON.parse(storedUserData);

                        // Now ping the server to see if the server side session is still valid
                        $http({
                           method: 'GET',
                           url: apiUrl + 'login/' + storedUser.userId
                        }).success(function(data, status) {
                           if (status === 200)
                           {
                              setUser(storedUser);
                           }
                           else
                           {
                              clearUser(LOGIN_EVENTS.LOGOUT_SUCCESS, "User Expired!");
                           }
                        }).error(function(data, status) {
                           clearUser(LOGIN_EVENTS.LOGOUT_SUCCESS, "User Expired!");
                        });
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
                     }).success(function(userInfo, status) {
                        if (status === 200)
                        {
//                           var userInfo = {
//                              userId: userId,
//                              accessLevel: data
//                           };
                           setUser(userInfo);
                        }
                        else
                        {
                           clearUser(LOGIN_EVENTS.LOGIN_FAILED, "Login Failure!");
                        }
                     }).error(function(data, status) {
                        clearUser(LOGIN_EVENTS.LOGIN_FAILED, data.details);
                     });
                  },
                  logout: function() {
                     $http({
                        method: 'GET',
                        url: apiUrl + 'logout'
                     }).success(function(data, status) {
                        clearUser(LOGIN_EVENTS.LOGOUT_SUCCESS, "User logged out!");
                     });
                  }
               };
            } ])
// securityInterceptor $http interceptor
// This will ensure sure that you can't make a service call without being authenticated            
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