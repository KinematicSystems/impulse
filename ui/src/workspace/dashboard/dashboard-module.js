angular.module('dashboardModule', [ 'services.DashboardService', 'services.ImpulseService'])

.controller(
      'DashboardController',
      [ '$scope', '$filter', 'LOGIN_EVENTS', 'COLLAB_EVENTS', 'impulseService', 'dashboardService', 
            function($scope, $filter, LOGIN_EVENTS, COLLAB_EVENTS, impulseService, dashboardService) {
               $scope.pageTitle = 'Dashboard';
               $scope.messages = [];
               $scope.alerts = [];
               $scope.userId = null;
               
               $scope.userId = impulseService.getCurrentUserId();
                
               $scope.alerts.push({
                  type: 'success',
                  msg: "This message is to demonstrate success."
               });
               $scope.alerts.push({
                  type: 'info',
                  msg: "This message is to demonstrate info."
               });
               $scope.alerts.push({
                  type: 'warning',
                  msg: "This message is to demonstrate warning!"
               });
               $scope.alerts.push({
                  type: 'danger',
                  msg: "This message is to demonstrate danger!"
               });
               $scope.alerts.push({
                  type: 'success',
                  msg: "This message is to demonstrate another success."
               });
               $scope.alerts.push({
                  type: 'danger',
                  msg: "This message is to demonstrate more danger!"
               });
               $scope.alerts.push({
                  type: 'info',
                  msg: "This message is to demonstrate more info!"
               });
               $scope.alerts.push({
                  type: 'warning',
                  msg: "This message is to demonstrate another warning!"
               });

               function loadDashboard() {
                  $scope.pageTitle = 'Dashboard for ' + $scope.userId;
               }

               function clear() {
                  $scope.messages = [];
                  $scope.pageTitle = 'Dashboard (not logged in)';
               }

               $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
                  $scope.userId = params.userId;
                  loadDashboard();
               });

               $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                  clear();
               });

               $scope.$on(COLLAB_EVENTS.MESSAGE, function(event, params) {
                  $scope.$apply(function() {
                     $scope.messages.push(params.body);
                  });
               });

               $scope.closeAlert = function(index) {
                  $scope.alerts.splice(index, 1);
               };

               // Init
               if ($scope.userId)
               {
                  loadDashboard();
               }

            } ]);