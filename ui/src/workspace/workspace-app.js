angular.module(
      'workspaceApp',
      [ 'ui.bootstrap', 'ngAnimate', 'impulseFilters', 'workspaceDirectives', 'topbarModule', 'explorerModule', 'forumpostModule',
            'settingsModule', 'forumManagerModule', 'loginModule', 'dashboardModule', 'mapModule', 'calendarModule',
            'services.LoginService', 'services.ImpulseService', 'services.EventService' ]).config(
      [ '$rootScopeProvider', function($rootScopeProvider) {
      } ])

.run([ '$window', 'loginService', 'eventService', function($window, loginService, eventService) {
   loginService.restoreSession();
   var eventParams = {
      debugMode: true,
   };

   eventService.init(eventParams);

   $window.addEventListener('beforeunload', function() {
      // If we do this this way a refresh will require the user to log in again!
      // loginService.logout();
   });
} ])

.controller(
      'WorkspaceController',
      [ '$scope', '$window', 'eventService', 'settingsService', 'impulseService', 'LOGIN_EVENTS', 'APP_EVENTS',
            function($scope, $window, eventService, settingsService, impulseService, LOGIN_EVENTS, APP_EVENTS) {

               $scope.sidebarCollapsed = false;
               $scope.forumExplorerVisible = false;
               var currentUserId = impulseService.getCurrentUserId();
               if (currentUserId)
               {
                  $scope.userId = currentUserId;
               }   
               $scope.currentPage = 'AllHidden';
               $scope.isPolling = false;
               $scope.isCollaborator = false;

               function initForUser(userId) {
                  $scope.isCollaborator = impulseService.isCollaborator();
                  $scope.currentPage = 'forumpost';
                  $scope.forumExplorerVisible = false;
                  eventService.connect($scope.userId);
                  if ($scope.isCollaborator)
                  {
                     settingsService.getSetting($scope.userId, "workspace", "hideExplorerOnLogin").success(function(data, status) {
                        $scope.forumExplorerVisible = (data === 'false');
                     });

                     settingsService.getSetting($scope.userId, "workspace", "collapseSidebarOnLogin").success(function(data, status) {
                        $scope.sidebarCollapsed = (data === 'true');
                     });
                  }
               }

               if ($scope.userId)
               {
                  initForUser($scope.userId);
               }
               else
               {
                  $scope.currentPage = 'login';
                  $scope.forumExplorerVisible = false;
               }
               //DEBUG
               //               $scope.$watch('currentPage', function(value) {
               //                   console.log("currentPage="+value);  
               //               });

               $scope.$on(LOGIN_EVENTS.NOT_AUTHENTICATED, function(event, params) {
                  $scope.isPolling = false;
                  eventService.setPolling($scope.userId, $scope.isPolling);
                  $scope.currentPage = 'login';
                  $scope.forumExplorerVisible = false;
                  $scope.isCollaborator = false;
              });

               $scope.$on(LOGIN_EVENTS.LOGIN_FAILED, function(event, params) {
                  $scope.userId = "";
                  $scope.forumExplorerVisible = false;
                  $scope.isCollaborator = false;
               });

               $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
                  $scope.userId = params.userId;
                  initForUser($scope.userId);
                  $scope.isPolling = true;
                  eventService.setPolling($scope.userId, $scope.isPolling);
               });

               $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                  $scope.currentPage = 'login';
                  $scope.forumExplorerVisible = false;
                  eventService.disconnect($scope.userId);
                  $scope.userId = "";
                  $scope.isPolling = false;
                  $scope.isCollaborator = false;
               });

               $scope.$on(APP_EVENTS.ACTIVATE_MODULE, function(event, moduleId) {
                  $scope.currentPage = moduleId;
               });

               $scope.synchronize = function() {
                  eventService.synchronize($scope.userId);
               };

               $scope.togglePolling = function() {
                  $scope.isPolling = !$scope.isPolling;
                  eventService.setPolling($scope.userId, $scope.isPolling);
               };

            } ]);