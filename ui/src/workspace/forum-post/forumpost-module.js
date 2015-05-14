angular.module('forumpostModule', [ 'services.EnrollmentService', 'services.ForumPostService', 'services.ImpulseService' ])

.controller(
      'ForumPostController',
      [ '$scope', 'LOGIN_EVENTS', 'COLLAB_EVENTS', 'ENROLLMENT_STATUS', 'enrollmentService', 'forumpostService', 'impulseService',
            function($scope, LOGIN_EVENTS, COLLAB_EVENTS, ENROLLMENT_STATUS, enrollmentService, forumpostService, impulseService) {
               $scope.postList = [];
               $scope.forumList = [];
               $scope.viewMode = 'O'; // [O]verview [P]ost view [E]dit post
               $scope.currentForum = {};
               $scope.isCollaborator = impulseService.isCollaborator();
               $scope.headingText = 'Forum Posts';
               $scope.post = {
                  id: '',
                  title: '',
                  content: ''
               };

               $scope.orderBy = "postingDate";
               var currentUserId = impulseService.getCurrentUserId();

               function loadPosts(forumId) {
                  forumpostService.getForumPosts(forumId).success(function(data, status) {
                     $scope.postList = data;
                  });
               }

               function loadForums() {
                  forumpostService.getOverviews().success(function(data, status) {
                     $scope.postList = [];
                     $scope.forumList = data;
                  });
               }

               if (currentUserId)
               {
                  loadForums();
               }

               $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
                  $scope.isCollaborator = impulseService.isCollaborator();
                  currentUserId = params.userId;
                  loadForums();
               });

               $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                  $scope.postList = [];
                  $scope.forumList = [];
                  $scope.isCollaborator = false;
               });

               $scope.$on(COLLAB_EVENTS.USER.REMOVED, function(event, sourceUserId, collabEvent) {
                  loadForums();
               });

               $scope.$on(COLLAB_EVENTS.FORUM.ENROLLMENT, function(event, sourceUserId, collabEvent) {
                  loadForums();
               });

               $scope.$on(COLLAB_EVENTS.FORUM.CHANGE, function(event, sourceUserId, collabEvent) {
                  //                if (collabEvent.params.changeType === COLLAB_EVENTS.FORUM.CT_DELETE)
                  //                {
                  //                   loadForums();
                  //                }
                  //                else
                  //                {
                  loadForums();
                  //                }
               });

               $scope.closePostEditor = function() {
                  var msg = "Are you sure you want exit the post editor?";

                  var dlg = impulseService.showConfirm("Confirm Exit", msg);

                  dlg.result.then(function(btn) {
                     // Yes
                     $scope.setViewMode('O');
                  }, function(btn) {
                     // No (Do Nothing)
                  });
               };
               
               $scope.setViewMode = function(val) {
                  $scope.viewMode = val;
                  if ($scope.viewMode === 'O')
                  {
                     $scope.headingText = 'Forum Posts';
                  }
                  else if ($scope.viewMode === 'P')
                  {
                     $scope.headingText = 'Forum Posts';
                  }
                  else if ($scope.viewMode === 'E')
                  {
                     $scope.headingText = 'Posting Editor';
                  }
               };

               $scope.showPosts = function(forum) {
                  $scope.currentForum = forum;
                  $scope.viewMode = 'P';
                  $scope.headingText = forum.name + " Postings";
                  loadPosts(forum.id);
               };

               $scope.newPost = function() {
                  $scope.viewMode = 'E';
                  $scope.headingText = $scope.currentForum.name + " - Create New Post";
                  $scope.post = {
                     id: '',
                     title: '',
                     content: '',
                     forumId: $scope.currentForum.id,
                     contentType: 'text/html'
                  };
               };

               $scope.readPost = function(forum, postId) {
                  $scope.currentForum = forum;
                  forumpostService.getPost(forum.id, postId).success(function(data, status) {
                     $scope.postList = [];
                     $scope.postList.push(data);
                     $scope.headingText = $scope.currentForum.name + " - Viewing Single Post";
                     $scope.viewMode = 'P';
                  });
               };

               $scope.createFirstPost = function(forum) {
                  $scope.currentForum = forum;
                  $scope.newPost();
               };

               $scope.savePost = function() {
                  if ($scope.post.id === '')
                  {
                     forumpostService.createPost($scope.post).success(function(data, status) {
                        $scope.post.id = data;
                     });
                  }
                  else
                  {
                     forumpostService.updatePost($scope.post).success(function(data, status) {
                     });
                  }
               };

               $scope.editPost = function(post) {
                  $scope.viewMode = 'E';
                  $scope.headingText = $scope.currentForum.name + " - Edit Post";
                  forumpostService.getPost(post.forumId, post.id).success(function(data, status) {
                     $scope.post = data;
                  });
               };

               $scope.joinRequest = function(forumId) {
                  enrollmentService.setForumEnrollment(forumId, currentUserId, ENROLLMENT_STATUS.PENDING).success(function(data, status) {
                     loadForums();
                  });
               };

            } ]);
