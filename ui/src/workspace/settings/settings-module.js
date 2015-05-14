angular.module('settingsModule', [ 'services.SettingsService', 'services.ImpulseService' ])

.directive('match', function() {

   return {
      require : 'ngModel',
      restrict : 'A',
      scope : {
         match : '='
      },
      link : function(scope, elem, attrs, ctrl) {
         scope.$watch(function() {
            var modelValue = ctrl.$modelValue || ctrl.$$invalidModelValue;
            return ((ctrl.$pristine && angular.isUndefined(modelValue)) || scope.match === modelValue);
         }, function(currentValue) {
            ctrl.$setValidity('match', currentValue);
         });
      }
   };
})

.controller('SettingsController',
      [ '$scope', 'LOGIN_EVENTS', 'settingsService', 'impulseService', function($scope, LOGIN_EVENTS, settingsService, impulseService) {
         $scope.settingsMap = {};
         $scope.passwordData = { oldPassword: '', password: ''};
         var currentUserId = impulseService.getCurrentUserId();
         
         function loadSettings(userId) {
            settingsService.getSettingsForDomain(userId, 'workspace').success(function(data, status) {
               var settings = data;
               for (var i = 0; i < settings.length; ++i)
               {
                  $scope.settingsMap[settings[i].settingKey] = settings[i].value;
               }
            });
         }

         if (currentUserId)
         {
            loadSettings(currentUserId);
         }

         $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
            currentUserId = params.userId;
            loadSettings(currentUserId);
         });

         $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
            $scope.settingsMap = {};
         });

         $scope.setBoolean = function(domain, key) {
            if ($scope.settingsMap[key] === "true")
            {
               $scope.settingsMap[key] = "false";
            }
            else
            {
               $scope.settingsMap[key] = "true";
            }

            settingsService.setSetting(currentUserId, domain, key, $scope.settingsMap[key]);
         };

         $scope.setString = function(domain, key, value) {
             settingsService.setSetting(currentUserId, domain, key, value);
         };

         $scope.changePassword = function() {
            if ($scope.userForm.$valid)
            {
               settingsService.changePassword(currentUserId, $scope.passwordData.oldPassword, $scope.passwordData.password)
               .success(function(data, status){
                  impulseService.showNotification("Password Changed", data.message);
               })
               .error(function(data, status){
                  impulseService.showError("Password Error", data.details);
               });
            }
            else
            {
               // Form was not valid
               impulseService.showError("Data Entry Error", "Please correct errors before saving!");
            }
         };
         
      } ]);
