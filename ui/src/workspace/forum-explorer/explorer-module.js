angular
      .module('explorerModule',
            [ 'ngAnimate', 'services.ForumService', 'services.EnrollmentService', 'services.ImpulseService', 'angularFileUpload' ])

      .controller(
            'ForumController',
            [
                  '$scope',
                  '$upload',
                  '$filter',
                  'LOGIN_EVENTS',
                  'COLLAB_EVENTS',
                  'ENROLLMENT_STATUS',
                  'forumService',
                  'enrollmentService',
                  'impulseService',
                  function($scope, $upload, $filter, LOGIN_EVENTS, COLLAB_EVENTS, ENROLLMENT_STATUS, forumService, enrollmentService,
                        impulseService) {
                     $scope.pathStack = [];
                     $scope.selectedFiles = [];
                     $scope.forumList = [];
                     $scope.editMode = false;
                     $scope.userMode = false;
                     $scope.inviteMode = false;
                     $scope.userList = [];
                     $scope.nodeList = [];
                     $scope.selectedForum = null;
                     var currentUserId = impulseService.getCurrentUserId();

                     // Upload 
                     var uploadRightAway = true;
                     var uploadCount = 0;

                     function loadForums() {
                        enrollmentService.getJoinedForums(currentUserId).success(function(data, status) {
                           // Filter list for only Joined status
                           $scope.forumList = data;

                           if ($scope.forumList.length > 0)
                           {
                              $scope.changeForum($scope.forumList[0].id, $scope.forumList[0].name);
                           }
                        });
                     }

                     /*
                      * Load a node without pushing to pathStack
                      */
                     function loadNode(node) {
                        forumService.getForumFileNodes(node.id).success(function(data, status) {
                           $scope.nodeList = data;
                        });
                     }

                     function currentFolder() {
                        return ($scope.pathStack[0]);
                     }

                     $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
                        currentUserId = params.userId;
                        loadForums();
                     });

                     $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                        clear();
                        currentUserId = null;
                     });

                     $scope.$on(COLLAB_EVENTS.FORUM.NODE_CHANGE, function(event, sourceUserId, collabEvent) {
                        if (collabEvent.params.changeType === COLLAB_EVENTS.FORUM.CT_CREATE)
                        {
                           // If the current view contains this node reload it.
                           if (currentFolder().id === collabEvent.params.parentId)
                           {
                              loadNode(currentFolder());
                           }
                        }
                        else if (collabEvent.params.changeType === COLLAB_EVENTS.FORUM.CT_UPDATE)
                        {
                           updateNodeName(collabEvent.params.id, collabEvent.params.name);
                           $scope.$apply(); // Update UI with array change
                        }
                        else if (collabEvent.params.changeType === COLLAB_EVENTS.FORUM.CT_DELETE)
                        {
                           // If the deleted node is in view remove it
                           for (var i = 0; i < $scope.nodeList.length; ++i)
                           {
                              var node = $scope.nodeList[i];
                              if (node.id === collabEvent.params.id)
                              {
                                 // Only show notification if the current user was not the source
                                 if (sourceUserId !== currentUserId)
                                 {
                                    impulseService.showNotification("File/Folder Deleted",
                                          "The file or folder named '" + node.name + "' was deleted by '" + sourceUserId + "'");
                                 }
                                 $scope.nodeList.splice(i, 1);
                                 $scope.$apply(); // Update UI with array change
                                 break;
                              }
                           }

                           // If the deleted node is in the path stack delete it and navigate up to parent
                           for (var p = 0; p < $scope.pathStack.length; ++p)
                           {
                              var pnode = $scope.pathStack[p];
                              if (pnode.id === collabEvent.params.id)
                              {
                                 // Only show notification if the current user was not the source
                                 if (sourceUserId !== currentUserId)
                                 {
                                    impulseService.showNotification("Folder Deleted",
                                          "The folder named '" + pnode.name + "' was deleted by '" + sourceUserId + "'");
                                 }
                                 $scope.changeNode(pnode.parentId);
                                 break;
                              }
                           }

                        }
                     });

                     $scope.$on(COLLAB_EVENTS.FORUM.CHANGE, function(event, sourceUserId, collabEvent) {
                        if ((collabEvent.params.changeType === COLLAB_EVENTS.FORUM.CT_DELETE) && 
                              ($scope.selectedForum.id === collabEvent.params.forumId) && 
                              (sourceUserId !== currentUserId))
                        {
                           impulseService.showNotification("Forum Deleted",
                                 "The forum '" + $scope.selectedForum.name + "' was deleted by '" + sourceUserId + "'");
                        }
                        loadForums();
                     });
                     
                     // Now being handled by USER.JOINED event               
                     //               $scope.$on(COLLAB_EVENTS.FORUM.ENROLLMENT, function(event, sourceUserId, collabEvent) {
                     //                  if (sourceUserId === currentUserId && collabEvent.params.enrollmentStatus === ENROLLMENT_STATUS.JOINED)
                     //                  {
                     //                     loadForums();
                     //                  }
                     //               });

                     $scope.$on(COLLAB_EVENTS.USER.REMOVED, function(event, sourceUserId, collabEvent) {
                        loadForums();
                     });

                     // This is received when the user creates a forum or an join request is approved
                     $scope.$on(COLLAB_EVENTS.USER.JOINED, function(event, sourceUserId, collabEvent) {
                        loadForums();
                     });

                     $scope.isUploading = function() {
                        return (uploadCount > 0);
                     };

                     function clear() {
                        $scope.forumList = [];
                        $scope.pathStack = [];
                        $scope.selectedFiles = [];
                        $scope.userList = [];
                        uploadCount = 0;
                        $scope.nodeList = [];
                        $scope.selectedForum = null;
                     }

                     $scope.changeForum = function(forumId, forumName) {
                        forumService.getForumFileNodes(forumId).success(function(data, status) {
                           $scope.selectedForum = {
                              id: forumId,
                              forumId: forumId,
                              name: forumName
                           };
                           $scope.nodeList = data;
                           $scope.pathStack = [];
                           $scope.pathStack.push({
                              id: forumId,
                              forumId: forumId,
                              name: forumName
                           });

                           if ($scope.userMode)
                           {
                              getUserList(forumId);
                           }
                        });
                     };

                     $scope.toggleEditMode = function() {
                        $scope.editMode = !$scope.editMode;
                     };

                     $scope.toggleUserMode = function() {
                        if ($scope.userMode === true)
                        {
                           $scope.userMode = false;
                        }
                        else
                        {
                           $scope.userMode = true;
                        }

                        if ($scope.userMode && ($scope.selectedForum !== null))
                        {
                           getUserList($scope.selectedForum.id);
                        }
                     };

                     $scope.toggleInviteMode = function() {
                        $scope.inviteMode = !$scope.inviteMode;
                        getUserList($scope.selectedForum.id);
                     };

                     $scope.inviteUser = function(user) {
                        //alert("Invite User: " + user.userId);
                        enrollmentService.setForumEnrollment($scope.selectedForum.id, user.userId, ENROLLMENT_STATUS.INVITED).success(
                              function(data, status) {
                                 getUserList($scope.selectedForum.id);
                              });
                     };

                     function getUserList(forumId) {
                        enrollmentService.getForumUsers(forumId).success(function(data, status) {
                           $scope.userList = data;
                        });
                     }

                     $scope.enrollmentString = function(statusCode) {
                        return impulseService.enrollmentString(statusCode);
                     };

                     $scope.openNode = function(node) {
                        forumService.getForumFileNodes(node.id).success(function(data, status) {
                           $scope.nodeList = data;
                           // Add nodes in reverse order
                           $scope.pathStack.unshift(node);
                        });
                     };

                     $scope.downloadNode = function(node) {
                        forumService.getForumFile(node.id).success(function(data, status) {
                           // Nothing to do at this point the service actually calls window.open() 
                        });
                     };

                     $scope.changeNode = function(nodeId) {
                        // The Mac OSX path stack contains the current folder so we
                        // must test
                        // to be consistent with that design. (reverse order)
                        if (nodeId === $scope.pathStack[0].id)
                        {
                           return;
                        }

                        forumService.getForumFileNodes(nodeId).success(function(data, status) {
                           $scope.nodeList = data;

                           // Pop nodes off the stack until we find this node
                           // Remove nodes in reverse order
                           var node = $scope.pathStack[0];
                           while (node.id !== nodeId)
                           {
                              $scope.pathStack.shift();
                              node = $scope.pathStack[0];
                           }
                        });
                     };

                     $scope.parentFolder = function() {
                        // Make sure there is a parent
                        if ($scope.pathStack.length < 2)
                        {
                           return;
                        }
                        var parentNode = $scope.pathStack[1];
                        $scope.changeNode(parentNode.id);
                     };

                     $scope.isFolder = function(node) {
                        return (node.contentType === '#folder');
                     };

                     $scope.addFolder = function() {
                        var dlg = impulseService.showPrompt("New Folder", "Folder Name", "Enter a name for the new folder", "");
                        dlg.result.then(function(promptValue) {
                           if (angular.equals(promptValue, ''))
                           {
                              impulseService.showError("Error", "No folder name was entered!");
                              return;
                           }

                           var parentNode = $scope.pathStack[0];

                           var folder = {
                              id: '', // set by server
                              forumId: parentNode.forumId,
                              parentId: parentNode.id,
                              name: promptValue,
                              contentType: '#folder'
                           };

                           forumService.createForumFolder(folder).success(function(data, status) {
                              // Refresh the parent node to show new folder
                              forumService.getForumFileNodes(parentNode.id).success(function(data, status) {
                                 $scope.nodeList = data;
                              });
                           });
                        }, function() {
                           // Canceled
                        });
                     };

                     deleteFolder = function(node) {
                        var currentNode = node;
                        var msg = "Are you sure you want to delete '" + currentNode.name + "' and all of its contents?";

                        var dlg = impulseService.showConfirm("Confirm Delete", msg);
                        dlg.result.then(function(btn) {
                           // Yes
                           forumService.deleteForumFileNode(currentNode.forumId, currentNode.id).success(function(data, status) {
                              forumService.getForumFileNodes(currentNode.parentId).success(function(data, status) {
                                 $scope.nodeList = data;
                              });
                           });
                        }, function(btn) {
                           // No (Do Nothing)
                        });
                     };

                     $scope.deleteNode = function(node) {
                        if (node.contentType === '#folder')
                        {
                           deleteFolder(node);
                        }
                        else
                        {
                           var msg = "Are you sure you want to delete '" + node.name + "'?";
                           var dlg = impulseService.showConfirm("Confirm Delete", msg);
                           dlg.result.then(function(btn) {
                              // Yes
                              forumService.deleteForumFileNode(node.forumId, node.id).success(function(data, status) {
                                 // Remove the folder from the pathStack and refresh list
                                 forumService.getForumFileNodes(node.parentId).success(function(data, status) {
                                    $scope.nodeList = data;
                                 });
                              });
                           }, function(btn) {
                              // No (Do Nothing)
                           });

                        }
                     };

                     function updateNodeName(nodeId, newName) {
                        /*
                         * Check to see if renamed forum is in the current list 
                         * and update name if it is.
                         */
                        for (var i = 0; i < $scope.nodeList.length; ++i)
                        {
                           var node = $scope.nodeList[i];
                           if (node.id === nodeId)
                           {
                              node.name = newName;
                              $scope.nodeList[i] = node;
                              break;
                           }
                        }
                     }

                     $scope.renameNode = function(node) {
                        var dlg = impulseService.showPrompt("Rename", "New Name", "Enter a new name for '" + node.name + "'", node.name);
                        dlg.result.then(function(promptValue) {
                           var newName = promptValue;
                           if (newName !== null && newName.length > 0 && !angular.equals(newName, node.name))
                           {
                              forumService.renameNode(node.forumId, node.id, newName).success(function(data, status) {
                                 //                           forumService.getForumFileNodes(node.parentId).success(function(data, status) {
                                 //                              $scope.nodeList = data;
                                 //                           });
                                 updateNodeName(node.id, newName);
                              });
                           }
                        }, function() {
                           // Canceled
                        });
                     };

                     $scope.onFileSelect = function($files) {
                        var i = 0;
                        $scope.selectedFiles = [];
                        $scope.progress = [];
                        $scope.upload = [];
                        $scope.uploadResult = [];
                        $scope.dataUrls = [];

                        $scope.selectedFiles = $files;
                        uploadCount = $files.length;
                        for (i = 0; i < $files.length; i++)
                        {
                           var $file = $files[i];
                           $scope.progress[i] = -1;
                           if (uploadRightAway)
                           {
                              $scope.start(i);
                           }
                        }
                     };

                     $scope.hasUploader = function(index) {
                        return $scope.upload[index] != null;
                     };

                     $scope.abort = function(index) {
                        if ($scope.upload.length > index)
                        {
                           $scope.upload[index].abort();
                           $scope.upload[index] = null;
                        }
                     };

                     $scope.start = function(index) {
                        $scope.progress[index] = 0;
                        $scope.errorMsg = null;
                        // var uploadURL = "forums/forum-upload.php";
                        var uploadURL = "../api/forum-upload";

                        $scope.upload[index] = $upload.upload({
                           url: uploadURL,
                           method: 'POST',
                           headers: {
                              'Content-Type': 'application/x-www-form-urlencoded'
                           },
                           data: currentFolder(),
                           file: $scope.selectedFiles[index],
                           fileFormDataName: 'file'
                        }).then(function(response) { // Success
                           if (!response.data.error)
                           {
                              // $scope.uploadResult.push(response.data);
                              --uploadCount;
                              if (uploadCount === 0)
                              {
                                 forumService.getForumFileNodes(currentFolder().id).success(function(data, status) {
                                    $scope.nodeList = data;
                                    $scope.selectedFiles = [];
                                 });
                              }
                           }
                           else
                           {/* TODO: add toastr or error dialog
                                                                                                                                                                                                      $scope.alerts.push({
                                                                                                                                                                                                         type: 'danger',
                                                                                                                                                                                                         msg: response.data.message
                                                                                                                                                                                                      });
                                                                                                                                                                                                      */
                           }
                        }, function(response) { // Error
                           if (response.status > 0)
                           {
                              /* TODO: add toastr or error dialog
                              $scope.alerts.push({
                                 type: 'danger',
                                 msg: response.data.message
                              });
                              */}
                        }, function(evt) { // Progress
                           // Math.min is to fix IE which reports 200% sometimes
                           $scope.progress[index] = Math.min(100, parseInt(100.0 * evt.loaded / evt.total, 10));
                        });
                     };

                     // Init
                     if (currentUserId)
                     {
                        loadForums();
                     }

                  } ]).directive('ksForumPath', function() {
         function linkFunc(scope, element, attrs) {
            var displayPath = "";

            scope.$watchCollection(attrs.ksForumPath, function(value) {
               var pathStack = value;
               // Rebuild the path (reverse order)
               displayPath = "";
               for (var i = pathStack.length - 1; i >= 0; --i)
               {
                  displayPath += "/" + pathStack[i].name;
               }

               updateUI();
            });

            function updateUI() {
               element.text(displayPath);
            }
         }

         return {
            link: linkFunc,
            restrict: 'A'
         };
      });

// forumModule.directive('ksForumPath', function() {
// return {
// restrict: 'E',
// scope: {
// pathStack: '=path'
// },
// templateUrl: 'forums/ks-forumpath.html'
// };
// });

