angular.module('services.ForumService', []).factory('forumService', [ '$http', function($http) {
   var apiUrl = '../api/';
   var apiSection = 'forums';

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
      getAllForums: function() {
         return runGetRequest(apiSection + "/admin");
      },

      getJoinedForums: function() {
         return runGetRequest(apiSection + "/enroll/joined");
      },
      getInvitations: function() {
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + "/enroll/invitations"
         });
      },
      getForumFileNodes: function(nodeId) {
         return runGetRequest(apiSection + '/' + nodeId);
      },
      createForumFolder: function(folder) {
         return $http({
            method: 'POST',
            data: folder,
            url: apiUrl + apiSection + "/folder"
         });
      },
      deleteForum: function(forumId) {
         return $http({
            method: 'DELETE',
            url: apiUrl + apiSection + '/admin/' + forumId
         });
      },
      renameNode: function(forumId,nodeId, name) {
         return $http({
            method: 'PUT',
            data: {
               forumId: forumId,
               nodeName: name
            },
            url: apiUrl + apiSection + '/file/' + nodeId
         });
      },
      deleteForumFolder: function(forumId, folderId) {
         return $http({
            method: 'DELETE',
            url: apiUrl + apiSection + '/folder/' + forumId + '/' + folderId
         });
      },
      getForumFile: function(fileId) {
         // For Now...
         window.open(apiUrl + apiSection + '/file/' + fileId, '_blank', '');
         //         return $http({
         //            method: 'GET',
         //            url: apiUrl + apiSection + '/file/' + fileId
         //         }); 
      },
      deleteForumFile: function(forumId, fileId) {
         return $http({
            method: 'DELETE',
            url: apiUrl + apiSection + '/file/' + forumId + '/' + fileId
         });
      },
      getForumUsers: function(forumId, enrolled) {
         var enrollState = (enrolled) ? "/enrolled/" : "/not-enrolled/";
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + enrollState + forumId
         });
      },
      getPendingJoinRequests: function() {
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + "/enroll/pending"
         });
      },
      createForum: function(forumName, description, userId) {
         var params = {
               name: forumName,
               description: description,
               userId: userId
            };

         return $http({
            method: 'POST',
            data: params,
            url: apiUrl + apiSection + "/admin"
         });
      },
      updateForum: function(forum, userId) {
         var params = {
               name: forum.name,
               description: forum.description,
               userId: userId
            };

         return $http({
            method: 'PUT',
            data: params,
            url: apiUrl + apiSection + "/admin/" + forum.id
         });
      },
      setForumEnrollment: function(forumId, userId, enrollmentStatus) {
         var params = {
            forumId: forumId,
            userId: userId,
            enrollmentStatus: enrollmentStatus
         };

         return $http({
            method: 'POST',
            data: params,
            url: apiUrl + apiSection + "/enrollment"
         });
      }

   };
} ]);
