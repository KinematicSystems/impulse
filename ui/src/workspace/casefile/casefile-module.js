angular.module('casefileModule', ['textAngular','services.CasefileService', 'services.ImpulseService'])

.controller('CasefileController', ['$scope','LOGIN_EVENTS','casefileService',
		function($scope, LOGIN_EVENTS, casefileService) {
			$scope.pageTitle = 'Casefile';

		    $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
		        var userId = params;
		    	$scope.pageTitle = 'Casefile for ' + userId;
			});    

		    $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
				$scope.pageTitle = 'Casefile (not logged in)';
			});    
		} 
]);

