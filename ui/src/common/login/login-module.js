angular.module('loginModule', [ 'services.LoginService', 'services.ImpulseService' ])


.controller('LoginController', [ '$scope', 'loginService', 'LOGIN_EVENTS',
	function($scope, loginService, LOGIN_EVENTS) {
		$scope.userId = "";
		$scope.password = "";
		$scope.loginError = undefined;

	    $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
				$scope.loginError = undefined;
		});    

	    $scope.$on(LOGIN_EVENTS.LOGIN_FAILED, function(event, params) {
			$scope.loginError = params;
	    });    
	    
		$scope.userLogin = function() {
		    if ($scope.userForm.$valid) 
		    {
		    	loginService.login($scope.userId, $scope.password);
		    } 
		    else 
		    {
		    	// Form was not valid 
		       confirm("Please correct errors.");   
			}
		};
	} ]);
