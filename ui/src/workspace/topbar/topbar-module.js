angular
      .module('topbarModule', [ 'toastr', 'services.ImpulseService', 'services.LoginService' ])
      .controller(
            'TopbarController',
            [
                  '$scope',
                  '$filter',
                  'toastr',
                  'LOGIN_EVENTS',
                  'COLLAB_EVENTS',
                  'ENROLLMENT_STATUS',
                  'impulseService',
                  'loginService',
                  function($scope, $filter, toastr, LOGIN_EVENTS, COLLAB_EVENTS, ENROLLMENT_STATUS, impulseService, loginService) {
                     $scope.userId = impulseService.getCurrentUser();
                     $scope.isCollaborator = impulseService.isCollaborator();
                     $scope.statCounts = [ 22, 0, 0, 14, 0, 12, 0 ]; // for demo of top status badges
                     $scope.messageCount = 0;
                     $scope.dashboardCount = 0;

                     function clear() {
                        $scope.messageCount = 0;
                        $scope.dashboardCount = 0;
                        $scope.userId = null;
                     }

                     $scope.logout = function() {
                        loginService.logout();
                     };

                     $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
                        $scope.userId = params.userId;
                        $scope.isCollaborator = impulseService.isCollaborator();
                     });

                     $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                        clear();
                     });

                     $scope.$on(COLLAB_EVENTS.MESSAGE, function(event, params) {
                        $scope.$apply(function() {
                           // Need to do $apply because angular doesn't bind primitives
                           $scope.messageCount++;
                        });
                     });

                     $scope.$on(COLLAB_EVENTS.USER.INVITE,
                        function(event, sourceUserId, collabEvent) {
                           var msg = "You have been invited to the forum '" + collabEvent.params.forumName + "' by " + collabEvent.params.sourceUserId;
                           toastr.info(msg, 'Forum Invitation', {
                              closeButton: true, positionClass: 'toast-bottom-right'
                           });
                           // Need to do $apply because angular doesn't watch/bind non string data (I think: mattg)
                           //$scope.$apply(function() {
                              $scope.dashboardCount++;
                           //});
                        });
                  } ]);