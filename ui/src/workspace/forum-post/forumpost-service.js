angular.module('services.ForumPostService', []).factory('forumpostService', [ '$http', function($http) {
   var apiUrl = '../api/';
   var apiSection = 'posting';

   var runGetRequest = function(method) {
      // Return the promise from the $http service 
      // that calls the admin user API 
      return $http({
         method: 'GET',
         url: apiUrl + method
      });
   };
   // Return the service object functions
   return {
      getForumPosts: function(forumId) {
         return runGetRequest(apiSection + '/' + forumId + '/post');
      },
      getPost: function(forumId,postId) {
         return runGetRequest(apiSection + '/' + forumId + '/post/' + postId);
      },
      getSummary: function(forumId) {
         return runGetRequest(apiSection + '/' + forumId + '/post-summary');
      },
      getOverviews: function() {
         return runGetRequest('/posting-overviews');
      },
      createPost: function(posting) {
         return $http({
            method: 'POST',
            data: posting,
            url: apiUrl + apiSection + "/" + posting.forumId + "/post"
         });
      },
      updatePost: function(posting) {
         return $http({
            method: 'PUT',
            data: posting,
            url: apiUrl + apiSection + "/" + posting.forumId + "/post/" + posting.id
         });
      }
   };
} ]);
