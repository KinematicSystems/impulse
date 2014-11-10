var userModule = angular.module('userModule', [ 'services.UserService', 'services.ImpulseService', 'ngTable' ]);

userModule.controller('UserController', [
      '$scope',
      '$location',
      'userService',
      'impulseService',
      function($scope, $location, userService, impulseService) {
         $scope.userOrder = 'lastName'; // Default sort order

         userService.getAllUsers().success(function(data, status) {
            $scope.userList = data;
         });

         $scope.editUser = function(id) {
            for (var i = 0; i < $scope.userList.length; ++i)
            {
               if ($scope.userList[i].id === id)
               {
                  $location.path('/users/' + id);
               }
            }
         };

         $scope.newUser = function() {
            $location.path('/users/new-user');
         };

         $scope.deleteUser = function(user, $index, $event) {
            // Don't let the click bubble up to the ng-click on the
            // enclosing div, which will try to trigger
            // an edit of this item.
            $event.stopPropagation();
            var dlg = impulseService.showConfirm("Confirm Delete",
                  "Are you sure you want to delete user '" + user.id + "'?");
            dlg.result.then(function(btn) {
               // Yes
               userService.deleteUser(user.id).success(function(data, status) {
                  if (status === 204 || status === 200) // 204 = No content
                  // success
                  {
                     // It is gone from the DB so we can remove it from the
                     // local list too
                     // because of sorting the user can't be removed by index
                     for (var i = 0; i < $scope.userList.length; ++i)
                     {
                        if ($scope.userList[i].id === user.id)
                        {
                           $scope.userList.splice(i, 1);
                           break;
                        }
                     }

                  }
               });
            }, function(btn) {
               // No (Do Nothing)
            });
         };

         $scope.editProperties = function(user, $index, $event) {
            // Don't let the click bubble up to the ng-click on the
            // enclosing div, which will try to trigger
            // an edit of this item.
            $event.stopPropagation();
            $location.path('/users/' + user.id + '/properties');
         };

         $scope.orderProp = 'lastName';
      } ]);

userModule.controller('UserEditController', [
      '$scope',
      '$routeParams',
      '$location',
      'impulseService',
      'userService',
      function($scope, $routeParams, $location, impulseService, userService) {
         $scope.editMode = false;
         $scope.user = {};

         if ($routeParams.id === "new-user")
         {
            // This will ultimately make a new user default to true
            $scope.user = {};
            $scope.userEnabled = true;
            $scope.sysAdmin = false;
            $scope.sysUser = true;

         }
         else
         {
            userService.getUser($routeParams.id).success(function(data, status) {
               $scope.user = data;
               $scope.userEnabled = $scope.user.enabled;
               $scope.sysAdmin = $scope.user.sysadmin;
               $scope.sysUser = $scope.user.sysuser;
               $scope.editMode = true;
            });
         }

         // Need this because angular checkboxes wont directly update the
         // model
         $scope.$watch('userEnabled', function(value) {
            $scope.user.enabled = value;
         });

         $scope.$watch('sysAdmin', function(value) {
            $scope.user.sysadmin = value;
         });

         $scope.$watch('sysUser', function(value) {
            $scope.user.sysuser = value;
         });
        
         $scope.saveUser = function() {
            if ($scope.userForm.$valid)
            {

               // Passed client side validations
               if ($scope.editMode)
               {
                  userService.updateUser($scope.user).success(
                        function(data, status) {
                           if (status === 200)
                           {
                              $location.path('/users');
                           }
                           else
                           {
                              impulseService.showError("Error saving user",
                                    "Update call was sucessful but returned an error!");
                           }
                        }).error(function(data, status, headers, config) {
                     impulseService.showError(data.message, data.details);
                     // alert("Code:"+ data.code + "
                     // message:"+data.message+"
                     // details:"+data.details);
                  });

               }
               else
               {
                  userService.createUser($scope.user).success(
                        function(data, status) {
                           if (status === 200)
                           {
                              $location.path('/users');
                           }
                           else
                           {
                              impulseService.showError("Error saving user",
                                    "Create call was sucessful but returned an error!");
                           }
                        }).error(function(data, status) {
                     impulseService.showError(data.message, data.details);
                     // alert("Code:"+ data.code + "
                     // message:"+data.message+"
                     // details:"+data.details);
                  });
               }
            }
            else
            {
               // Form was not valid
               impulseService.showError("Data Entry Error", "Please correct errors before saving!");
            }
         };

         $scope.cancelEdit = function() {
            impulseService.showConfirm("Confirm Cancellation", "Are you sure you want to cancel editing?").result.then(
                  function(btn) {
                     // Yes
                     $location.path('/users');
                  }, function(btn) {
                     // No (Do Nothing)
                  });
         };

      } ]);

/**
 * match directive Used to confirm password From
 * http://ngmodules.org/modules/angular-input-match
 */
userModule.directive('match', function() {

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
});

userModule.controller('UserPropertiesController', [ '$scope', '$routeParams', '$location', 'userService',
      'ngTableParams', function($scope, $routeParams, $location, userService, ngTableParams, $sce) {
         $scope.userId = $routeParams.id;
         $scope.properties = {};
         $scope.tableTitle = "Property assignments for user '" + $scope.userId + "'";

         /* jshint ignore:start */
         $scope.tableParams = new ngTableParams({
            page : 1, // show first page
            count : 200, // count per page
            sorting : {
               type : "asc" // initial sorting
            }
         }, {
            groupBy : function(item) {
               return item.section;
            },
            counts : [], // hide page counts
            // control
            total : 1, // value less than count
            // hide pagination

            getData : function($defer, params) {
               userService.getAllProperties().success(function(data, status) {
                  $scope.properties = data;
                  params.total(data.length);
                  $defer.resolve($scope.properties);
                  // Check any
                  // assigned
                  // properties
                  userService.getUserProperties($scope.userId).success(function(data, status) {
                     var userProps = data;
                     for (var j = 0; j < $scope.properties.length; ++j)
                     {
                        $scope.properties[j].checked = false;

                        for (var i = 0; i < userProps.length; ++i)
                        {
                           if (userProps[i] === $scope.properties[j].id)
                           {
                              $scope.properties[j].checked = true;
                              break;
                           }
                        }
                     }
                  });
               });
            }
         });
         /* jshint ignore:end */

         $scope.updateProperty = function(property) {
            if (property.checked)
            {
               userService.assignProperty($scope.userId, property.id).success(function(data, status) {
               });
            }
            else
            {
               userService.revokeProperty($scope.userId, property.id).success(function(data, status) {
               });
            }
         };

         $scope.close = function() {
            $location.path('/users');
         };

      } ]);
