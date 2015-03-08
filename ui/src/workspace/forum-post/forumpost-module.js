angular.module('forumpostModule', [ 'services.ForumService', 'services.ForumPostService', 'services.ImpulseService' ])

.controller(
      'ForumPostController',
      [ '$scope', 'LOGIN_EVENTS', 'COLLAB_EVENTS', 'ENROLLMENT_STATUS', 'forumService', 'forumpostService', 'impulseService',
            function($scope, LOGIN_EVENTS, COLLAB_EVENTS, ENROLLMENT_STATUS, forumService, forumpostService, impulseService) {
               $scope.postList = [];
               $scope.forumList = [];
               $scope.viewMode = 'O'; // [O]verview [P]ost view [E]dit post
               $scope.currentForumName = "";
               $scope.currentForumId = "";
               $scope.headingText = 'Forum Posts';
               $scope.post = {
                  id: '',
                  title: '',
                  content: ''
               };

               $scope.orderBy = "postingDate";
               var currentUserId = impulseService.getCurrentUser();

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
                  loadForums();
               });

               $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                  $scope.postList = [];
                  $scope.forumList = [];
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

               $scope.showPosts = function(forumId, forumName) {
                  $scope.currentForumName = forumName;
                  $scope.currentForumId = forumId;
                  $scope.viewMode = 'P';
                  $scope.headingText = forumName + " Postings";
                  loadPosts(forumId);
               };

               $scope.newPost = function() {
                  $scope.viewMode = 'E';
                  $scope.headingText = $scope.currentForumName + " - Create New Post";
                  $scope.post = {
                     id: '',
                     title: '',
                     content: '',
                     forumId: $scope.currentForumId,
                     contentType: 'text/html'
                  };
               };

               $scope.readPost = function(forumId, forumName, postId) {
                  $scope.currentForumName = forumName;
                  $scope.currentForumId = forumId;
                  forumpostService.getPost(forumId, postId).success(function(data, status) {
                     $scope.postList = [];
                     $scope.postList.push(data);
                     $scope.headingText = $scope.currentForumName + " - Viewing Single Post";
                     $scope.viewMode = 'P';
                  });
               };

               $scope.createFirstPost = function(forumId, forumName) {
                  $scope.currentForumName = forumName;
                  $scope.currentForumId = forumId;
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
                  $scope.headingText = $scope.currentForumName + " - Edit Post";
                  forumpostService.getPost(post.forumId, post.id).success(function(data, status) {
                     $scope.post = data;
                  });
               };

               $scope.joinRequest = function(forumId) {
                  forumService.setForumEnrollment(forumId, currentUserId, ENROLLMENT_STATUS.PENDING).success(function(data, status) {
                     loadForums();
                  });
               };

            } ]);
