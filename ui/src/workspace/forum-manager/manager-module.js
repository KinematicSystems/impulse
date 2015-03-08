angular
      .module('forumManagerModule', [ 'dialogs.main', 'services.ForumManagerService', 'services.ImpulseService', 'services.ForumService' ])

      .controller(
            'ForumManagerController',
            [
                  '$scope',
                  'dialogs',
                  'LOGIN_EVENTS',
                  'COLLAB_EVENTS',
                  'ENROLLMENT_STATUS',
                  'impulseService',
                  'forumManagerService',
                  'forumService',
                  function($scope, dialogs, LOGIN_EVENTS, COLLAB_EVENTS, ENROLLMENT_STATUS, impulseService, forumManagerService,
                        forumService) {
                     var currentUserId = impulseService.getCurrentUser();
                     $scope.editMode = 'M';
                     $scope.headingText = '';
                     $scope.inviteList = [];
                     $scope.inviteForumId = '';

                     $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
                        currentUserId = params;
                     });

                     $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                     });

                     $scope.$on(COLLAB_EVENTS.USER.REMOVED, function(event, sourceUserId, collabEvent) {
                     });

                     $scope.$on(COLLAB_EVENTS.FORUM.ENROLLMENT, function(event, sourceUserId, collabEvent) {
                     });

                     $scope.enrollmentString = function(statusCode) {
                        return impulseService.enrollmentString(statusCode);
                     };

                     $scope.changeEditMode = function(mode) {
                        $scope.editMode = mode;
                     };

                     $scope.editForum = function(forum) {
                        var params;

                        if (!forum)
                        {
                           params = {
                              heading: "Create New Forum",
                              forum: {
                                 id: '',
                                 name: '',
                                 description: ''
                              }
                           };
                        }
                        else
                        {
                           params = {
                              heading: "Edit Forum",
                              forum: forum,
                           };
                        }

                        var dlg = dialogs.create('./forum-manager/forum-edit-dialog.html', 'editForumDialogCtrl', params, {
                           size: 'sm'
                        });

                        //impulseService.showPrompt("Create New Forum", "Forum Name", "Enter a name for the new forum", "");
                        dlg.result.then(function(forumData) {
                           if (angular.equals(forumData.name, ''))
                           {
                              impulseService.showError("Error", "No forum name was entered!");
                              return;
                           }

                           if (angular.equals(forumData.description, ''))
                           {
                              impulseService.showError("Error", "No forum description was entered!");
                              return;
                           }

                           if (!forum)
                           {
                              forumService.createForum(forumData.name, forumData.description, currentUserId).success(
                                    function(forum, status) {
                                       impulseService.showNotification("Forum Created", "'" + forumData.name + "' successfully created.");
                                    });
                           }
                           else
                           {
                              forum.name = forumData.name;
                              forum.description = forumData.description;
                              forumService.updateForum(forum, currentUserId).success(function(forum, status) {
                                 impulseService.showNotification("Forum Updated", "'" + forumData.name + "' successfully updated.");
                              });
                           }
                        }, function() {
                           // Canceled
                        });
                     };

                     $scope.deleteForum = function(forumId, forumName) {
                        var msg = "Are you sure you want to delete '" + forumName + "'?<br>If this forum contains any data it will be archived rather than deleted.";

                        var dlg = impulseService.showConfirm("Confirm Delete", msg);

                        dlg.result.then(function(btn) {
                           // Yes
                           forumService.deleteForum(forumId).success(function(data, status) {
                              impulseService.showNotification("Forum Deleted", "'" + forumName + "' successfully deleted.");
                           });
                        }, function(btn) {
                           // No (Do Nothing)
                        });
                     };

                     function getInviteList() {
                        forumService.getForumUsers($scope.inviteForumId, false).success(function(data, status) {
                           $scope.inviteList = data;
                        });
                     }

                     $scope.inviteForum = function(forumId, forumName) {
                        $scope.headingText = ": Send invitations to '" + forumName + "'";
                        $scope.changeEditMode('I');
                        $scope.inviteForumId = forumId;
                        getInviteList();
                     };

                     $scope.inviteUser = function(user) {
                        forumService.setForumEnrollment($scope.inviteForumId, user.userId, ENROLLMENT_STATUS.INVITED).success(
                              function(data, status) {
                                 getInviteList();
                              });
                     };

                     $scope.leaveForum = function(forumId, forumName) {
                        var msg = "Are you sure you want to leave the forum '" + forumName + "'?";

                        var dlg = impulseService.showConfirm("Confirm Leave", msg);
                        dlg.result.then(function(btn) {
                           // Yes
                           forumService.setForumEnrollment(forumId, currentUserId, ENROLLMENT_STATUS.LEFT);
                        }, function(btn) {
                           // No (Do Nothing)
                        });
                     };
                  } ]).controller(
            'editForumDialogCtrl',
            function($scope, $modalInstance, data) {
               // don't call the parameters params, keep it as data otherwise a bug is created! (mattg)
               $scope.forumName = data.forum.name;
               $scope.forumDescription = data.forum.description;
               $scope.dialogHeading = data.heading;

               $scope.cancel = function() {
                  $modalInstance.dismiss('Canceled');
               }; // end cancel

               $scope.save = function() {
                  $modalInstance.close({
                     name: $scope.forumName,
                     description: $scope.forumDescription
                  });
               }; // end save

               $scope.hitEnter = function(evt) {
                  if (angular.equals(evt.keyCode, 13) && !(angular.equals($scope.forumName, null)) && !(angular.equals(
                        $scope.forumDescription, null)) || (angular.equals($scope.forumName, '') && angular.equals($scope.forumDescription,
                        '')))
                  {
                     $scope.save();
                  }
               };
            }) // end controller(createForumDialogCtrl)
;

/*
      .controller('inviteForumDialogCtrl', function($scope, $modalInstance, forumService, ENROLLMENT_STATUS, data) {
         var forumId = data.forumId;
         $scope.dialogHeading = "Invite to forum '" + data.forumName + "'";
         $scope.userList = [];

         function getUserList() {
            forumService.getForumUsers(forumId, false).success(function(data, status) {
               $scope.userList = data;
            });
         }

         $scope.inviteUser = function(user) {
            //alert("Invite User: " + user.userId);
            forumService.setForumEnrollment(forumId, user.userId, ENROLLMENT_STATUS.INVITED).success(
                  function(data, status) {
                     getUserList(forumId);
                  });
         };
         
         $scope.cancel = function() {
            $modalInstance.dismiss('Canceled');
         }; // end cancel

         getUserList();
      }) // end controller(createForumDialogCtrl)

                     $scope.inviteForum = function(forumId, forumName) {
                         var params = {
                           forumId: forumId,
                           forumName: forumName
                        };

                        var dlg = dialogs.create('./forum-manager/forum-invite-dialog.html', 'inviteForumDialogCtrl', params, {
                           size: 'md'
                        });

                        dlg.result.then(function(forumData) {
                        }, function() {
                           // Canceled
                        });
                     };
*/