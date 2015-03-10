angular.module('workspaceDirectives').directive('enrollmentList',
            ['$document','impulseService','forumService','LOGIN_EVENTS','COLLAB_EVENTS','ENROLLMENT_STATUS',
function($document, impulseService, forumService, LOGIN_EVENTS, COLLAB_EVENTS, ENROLLMENT_STATUS) {
   return {
      restrict: 'A',
      scope: {
         enrollmentStatus: '@enrollmentStatus',
         onInvite: '&',
         onLeave: '&',
         onEdit: '&',
         onDelete: '&'
      },
      controller: function($scope) {
         $scope.enrollmentList = [];

         $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
            updateList();
         });

         function updateList() {
            if ($scope.enrollmentStatus === ENROLLMENT_STATUS.INVITED)
            {
               loadInvites();
            }
            else if ($scope.enrollmentStatus === ENROLLMENT_STATUS.PENDING)
            {
               loadJoinRequests();
            }
            else if ($scope.enrollmentStatus === ENROLLMENT_STATUS.JOINED)
            {
               loadJoinedForums();
            }
         }

         function loadInvites() {
            forumService.getInvitations().success(function(data, status) {
               $scope.enrollmentList = data;
            });
         }

         function loadJoinRequests() {
            forumService.getPendingJoinRequests().success(function(data, status) {
               $scope.enrollmentList = data;
            });
         }

         function loadJoinedForums() {
            forumService.getJoinedForums().success(function(data, status) {
               $scope.enrollmentList = data;
            });
         }

         $scope.acceptEnrollment = function(forumId, userId) {
            var newStatus = "ERROR";
            if ($scope.enrollmentStatus === ENROLLMENT_STATUS.INVITED)
            {
               newStatus = ENROLLMENT_STATUS.ACCEPTED;
            }
            else if ($scope.enrollmentStatus === ENROLLMENT_STATUS.PENDING)
            {
               newStatus = ENROLLMENT_STATUS.JOINED;
            }

            return forumService.setForumEnrollment(forumId, userId, newStatus);
         };

         $scope.inviteForum = function(forumId, forumName) {
            $scope.onInvite()(forumId, forumName);
         };

         $scope.leaveForum = function(forumId, forumName) {
            $scope.onLeave()(forumId, forumName);
         };

         $scope.editForum = function(forum) {
            $scope.onEdit()(forum);
         };

         $scope.deleteForum = function(forumId, forumName) {
            $scope.onDelete()(forumId, forumName);
         };

         $scope.$on(COLLAB_EVENTS.USER.INVITE, function(event, sourceUserId, collabEvent) {
            loadInvites();//$scope.enrollmentList.push(collabEvent.params);
         });

         $scope.$on(COLLAB_EVENTS.FORUM.ENROLLMENT,
            function(event, sourceUserId, collabEvent) {
               //TODO get rid of user ID in service, this should work for current user only 

               if ((collabEvent.params.enrollmentStatus === ENROLLMENT_STATUS.JOINED || collabEvent.params.enrollmentStatus === ENROLLMENT_STATUS.DECLINED) && sourceUserId === impulseService
                     .getCurrentUser())
               {
                  // Remove the invite from the list on server confirmation
                  for (var i = 0; i < $scope.enrollmentList.length; ++i)
                  {
                     if ($scope.enrollmentList[i].forumId === collabEvent.params.forumId)
                     {
                        $scope.enrollmentList.splice(i, 1);
                        break;
                     }
                  }
               }
            });

         $scope.$on(COLLAB_EVENTS.FORUM.CHANGE, function(event, sourceUserId, collabEvent) {
            //TODO Make updates more efficient 
            if (collabEvent.params.changeType === COLLAB_EVENTS.FORUM.CT_DELETE)
            {
               updateList();
            }
            else
            {
               updateList();
            }
         });

         // Init
         if (impulseService.getCurrentUser())
         {   
            updateList();
         }
      },
      templateUrl: 'directives/enrollmentlist/enrollmentlist.html',
      //templateUrl: 'templates/enrollmentlist/enrollmentlist.html',
      link: function(scope, element, attrs) {
      }
   };
} ])

      .run([ '$templateCache', function($templateCache) {
         'use strict';
         // Not used
         $templateCache.put('templates/enrollmentlist/enrollmentlist.html', "<div>{{enrollmentList}}</span>\n");

      } ]);