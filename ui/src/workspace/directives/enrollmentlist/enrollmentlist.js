angular
      .module('workspaceDirectives')
      .directive(
            'enrollmentList',
            [
                  'impulseService',
                  'enrollmentService',
                  'eventService',
                  'LOGIN_EVENTS',
                  'COLLAB_EVENTS',
                  'ENROLLMENT_STATUS',
                  function(impulseService, enrollmentService, eventService, LOGIN_EVENTS, COLLAB_EVENTS, ENROLLMENT_STATUS) {
                     return {
                        restrict: 'A',
                        scope: {
                           enrollmentStatus: '@enrollmentStatus',
                           enrollmentCount: '=enrollmentCount',
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
                                 loadEnrolledForums();
                              }
                              else if ($scope.enrollmentStatus === ENROLLMENT_STATUS.REJECTED)
                              {
                                 loadRejections();
                              }
                           }

                           function setCount(theList) {
                              if (typeof $scope.enrollmentCount !== "undefined")
                              {
                                 // Setting the count to "" instead of zero will cause the badge not to display
                                 if (theList.length > 0)
                                 {
                                    $scope.enrollmentCount.count = theList.length;
                                 }
                                 else
                                 {
                                    $scope.enrollmentCount.count = "";
                                 }
                              }
                           }

                           function loadInvites() {
                              enrollmentService.getInvitations(impulseService.getCurrentUserId()).success(function(data, status) {
                                 $scope.enrollmentList = data;
                                 setCount(data);
                              });
                           }

                           function loadJoinRequests() {
                              enrollmentService.getPendingJoinRequests(impulseService.getCurrentUserId()).success(function(data, status) {
                                 $scope.enrollmentList = data;
                                 setCount(data);
                              });
                           }

                           function loadEnrolledForums() {
                              enrollmentService.getEnrolled(impulseService.getCurrentUserId()).success(function(data, status) {
                                 $scope.enrollmentList = data;
                                 setCount(data);
                              });
                           }

                           function loadRejections() {
                              enrollmentService.getRejections(impulseService.getCurrentUserId()).success(function(data, status) {
                                 $scope.enrollmentList = data;
                                 setCount(data);
                              });
                           }

                           $scope.acceptInvite = function(forumId, userId) {
                              enrollmentService.acceptInvite(forumId, userId).success(function(data, status) {
                                 // If this is the current user accepting an invite we must subscribe to the
                                 // forum events.
                                 eventService.subscribeToForum(userId, forumId);
                              });
                           };

                           $scope.declineInvite = function(forumId, userId) {
                              enrollmentService.setForumEnrollment(forumId, userId, ENROLLMENT_STATUS.DECLINED).success(
                                    function(data, status) {
                                       updateList();
                                    });
                           };

                           $scope.approveJoinRequest = function(forumId, userId) {
                              enrollmentService.approveJoinRequest(forumId, userId).success(function(data, status) {
                              });
                           };

                           $scope.rejectJoinRequest = function(forumId, userId) {
                              enrollmentService.setForumEnrollment(forumId, userId, ENROLLMENT_STATUS.REJECTED).success(
                                    function(data, status) {
                                       updateList();
                                  });
                           };

                           $scope.removeRejection = function(forumId, userId) {
                              enrollmentService.removeRejection(forumId, userId).success(function(data, status) {
                                 updateList();
                              });
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
                              if ($scope.enrollmentStatus === ENROLLMENT_STATUS.INVITED)
                              {
                                 loadInvites();
                              }
                           });

                           $scope.$on(COLLAB_EVENTS.USER.JOINED, function(event, sourceUserId, collabEvent) {
                              updateList();
                           });

                           $scope.$on(COLLAB_EVENTS.USER.REJECTED, function(event, sourceUserId, collabEvent) {
                              updateList();
                           });

                           $scope.$on(COLLAB_EVENTS.USER.REMOVED, function(event, sourceUserId, collabEvent) {
                              updateList();
                           });
 
                           $scope
                                 .$on(
                                       COLLAB_EVENTS.FORUM.ENROLLMENT,
                                       function(event, sourceUserId, collabEvent) {
                                          var currentUserId = impulseService.getCurrentUserId();

                                          if ((collabEvent.params.enrollmentStatus === ENROLLMENT_STATUS.JOINED || collabEvent.params.enrollmentStatus === ENROLLMENT_STATUS.DECLINED || collabEvent.params.enrollmentStatus) && sourceUserId === currentUserId)
                                          {
                                             // Remove the invite from the list on server confirmation
                                             for (var i = 0; i < $scope.enrollmentList.length; ++i)
                                             {
                                                if ($scope.enrollmentList[i].forum.id === collabEvent.params.forumId)
                                                {
                                                   $scope.enrollmentList.splice(i, 1);
                                                   setCount($scope.enrollmentList);
                                                   break;
                                                }
                                             }
                                          }
                                          else if (collabEvent.params.enrollmentStatus === ENROLLMENT_STATUS.PENDING && sourceUserId !== currentUserId)
                                          {
                                             if ($scope.enrollmentStatus === ENROLLMENT_STATUS.PENDING)
                                             {
                                                loadJoinRequests();
                                             }
                                          }
                                       });

                           $scope.$on(COLLAB_EVENTS.FORUM.CHANGE, function(event, sourceUserId, collabEvent) {
                              //TODO Make updates more efficient? 
                              if (collabEvent.params.changeType === COLLAB_EVENTS.FORUM.CT_DELETE)
                              {
                                 updateList();
                              }
                              else
                              {
                                 updateList();
                              }
                           });

                           $scope.$on(COLLAB_EVENTS.USER.REMOVED, function(event, sourceUserId, collabEvent) {
                              updateList();
                           });
                           
                           // Init
                           if (impulseService.getCurrentUserId())
                           {
                              updateList();
                           }
                        },
                        templateUrl: 'directives/enrollmentlist/enrollmentlist.html',
                        //templateUrl: 'templates/enrollmentlist/enrollmentlist.html',
                        link: function(scope, element, attrs) {
                        }
                     };
                  } ]).run([ '$templateCache', function($templateCache) {
         'use strict';
         // Not used
         $templateCache.put('templates/enrollmentlist/enrollmentlist.html', "<div>{{enrollmentList}}</span>\n");

      } ]);