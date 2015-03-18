angular
      .module('workspaceDirectives')

      .directive('userLink', [ '$http', '$document', function($http, $document) {
         return {
            restrict: 'A',
            scope: {
               userId: '=userid'
            },
            controller: function($scope) {
               /*
                              function showDetails(user) {
                                 var body = $document.find('body').eq(0);
                                 var container = null;
                                 var nameField = null;
                                 var userLinks = document.getElementsByClassName("userLink-details");
                                 if (userLinks.length > 0)
                                 {
                                    container = userLinks[0];
                                    nameField = container.getElementsByTagName("span")[0];
                                 }
                                 else
                                 {
                                    container = angular.element('<div></div>');
                                    container.attr('id', 'user.' + user.id);
                                    container.addClass('userLink-details');
                                    container.css({
                                       'pointer-events': 'auto'
                                    });
                                    container.on('mouseleave', function() {
                                       container.remove();
                                       //alert("mouseleave");
                                    });

                                    container.append("<span class='userLink-name'>XXXXXXXX</span>");
               //                     nameField = document.getElementsByClassName("userLink-name");
               //                     nameField.css({
               //                        'width': '200px',
               //                        'height': '24px'
               //                     });
                                    body.append(container);
                                 }
               //                  nameField.innerHTML = "Hello " + user.firstName + " " + user.lastName;
                                 //alert("Hello " + user.firstName + " " + user.lastName);
                              }

                              $scope.getUserContent = function(userId) {
                                 return "Hello " + userId;
                              };
               */
               $scope.onMouseLeave = function($event) {
                  $scope.userModel = null;
               };

               $scope.chatWithUser = function(user) {
                  alert("This will open a chat session with '" + user.firstName + " " + user.lastName + "'")
               };

               $scope.linkClicked = function(userId) {
                  $http({
                     method: 'GET',
                     url: '../api/users/' + userId
                  }).success(function(data, status) {
                     $scope.userModel = data;
                     //showDetails(data);
                  });
               };
            },
            //templateUrl: 'templates/userLink/userLink.html',
            templateUrl: 'directives/userlink/userlink.html'  
         };
      } ])

      .run(
            [
                  '$templateCache',
                  function($templateCache) {
                     'use strict';
                     $templateCache
                           .put('templates/userLink/userLink.html',
                                 "<a href=\"\" ng-click=\"linkClicked(userId)\"><span class=\"fa fa-user\" style=\"padding-right:2px;\"></span>{{userId}}</a>");

                     //               "<a href=\"\" tooltip-trigger=\"focus\" tooltip-placement=\"right\" tooltip-html-unsafe=\"<div style='color: #111; background-color: #fff;'>User details and messaging buttons will be displayed here.</div>\"><span class=\"fa fa-user\" style=\"padding-right:2px;\"></span>{{userId}}</a>");

                     //                     "<a href=\"\" popover-placement=\"right\" popover-trigger=\"focus\" popover=\"<strong>User details</strong> and messaging buttons will be displayed here. Waiting for angular-bootstrap update that supports templates\"><span class=\"fa fa-user\" style=\"padding-right:2px;\"></span>{{userId}}</a>\n");
                  } ]);