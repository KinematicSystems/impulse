angular.module('adminApp', [ 'ui.bootstrap', 'ngRoute',
		'loginModule', 'services.LoginService', 'services.ImpulseService',
		'userModule', 'settingsModule', 'forumAdminModule' ])

.config([ '$routeProvider', '$locationProvider',
		function($routeProvider, $locationProvider) {
			$locationProvider.html5Mode(false);

			$routeProvider.when('/users', {
				templateUrl : 'users/users.html',
				controller : 'UserController'
			}).when('/login', {
				templateUrl : '../common/login/login.tpl.html',
				controller : 'LoginController'
			}).when('/users/:id', {
				templateUrl : 'users/user-edit.html',
				controller : 'UserEditController'
			}).when('/users/:id/properties', {
				templateUrl : 'users/user-properties.html',
				controller : 'UserPropertiesController'
			}).when('/settings', {
				templateUrl : 'settings/settings.html',
				controller : 'SettingsController'
			}).when('/forum', {
				templateUrl : 'forumadmin/forumadmin.html',
				controller : 'ForumAdminController'
			}).when('/', {
				redirectTo : '/users'
			}).otherwise({
				redirectTo : '/login'
			});

		} ])

/**
 * HeaderController
 * 
 * So far this this makes sure the current button is selected on the navigation
 * bar when the route changes.
 * 
 */
.controller('HeaderController', [
		'$scope',
		'$timeout',
		'$location',
		'$route',
		'loginService',
		'LOGIN_EVENTS',
		function($scope, $timeout, $location, $route, loginService,
				LOGIN_EVENTS) {
			$scope.loggedIn = false;

			$scope.$on(LOGIN_EVENTS.NOT_AUTHENTICATED, function(event, params) {
				$scope.loggedIn = false;
				$location.path('/login');
			});

			$scope.$on(LOGIN_EVENTS.LOGIN_FAILED, function(event, params) {
				$scope.loggedIn = false;
			});

			$scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
				$scope.loggedIn = true;
				$location.path('/');
			});

			$scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
				$scope.loggedIn = false;
				$location.path('/login');
			});

			$scope.isNavbarActive = function(navBarPath) {
				var pathElements = $location.path().split('/');
				return navBarPath === pathElements[1];
			};

			$scope.logout = function() {
				loginService.logout();
			};

			/*
			 * TODO Need to find a way to do this after the controllers are
			 * initialized Basically what is happening is the login service is
			 * firing the loginSuccess method before the listeners are added in
			 * the controllers in many but not all cases.
			 */
			$timeout(function() {
				loginService.restoreSession();
			}, 500);

		} ]);
