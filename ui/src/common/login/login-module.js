angular.module('loginModule', [ 'services.LoginService', 'services.ImpulseService' ])


.controller('LoginController', [ '$scope', 'loginService', 'LOGIN_EVENTS','impulseService',
	function($scope, loginService, LOGIN_EVENTS, impulseService) {
		$scope.userId = "";
		$scope.password = "";
		$scope.loginError = undefined;

	    $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
				$scope.loginError = undefined;
		      $scope.userId = "";
		      $scope.password = "";
		      if (typeof $scope.userForm !== "undefined")
		      {   
		         $scope.userForm.$setPristine();
		      }
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
		       impulseService.showError("Error", "Please correct errors.");   
		    }
		};
	} ]);
