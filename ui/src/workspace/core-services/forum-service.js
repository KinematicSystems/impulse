angular.module('services.ForumService', []).factory('forumService', [ '$http', function($http) {
   var apiUrl = '../api/';
   var apiSection = 'forums';
   var fileApiSection = 'forum-files';

   var runGetRequest = function(method) {
      // Return the promise from the $http service 
      // that calls the admin API 
      return $http({
         method: 'GET',
         url: apiUrl + method
      });
   };

   // Return the service object functions
   return {
      getAllForums: function() {
         return runGetRequest(apiSection);
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
            url: apiUrl + apiSection
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
            url: apiUrl + apiSection + "/" + forum.id
         });
      },
      deleteForum: function(forumId) {
         return $http({
            method: 'DELETE',
            url: apiUrl + apiSection + '/' + forumId
         });
      },
// FORUM FILE METHODS
      getForumFileNodes: function(nodeId) {
         return runGetRequest(fileApiSection + '/' + nodeId);
      },
      createForumFolder: function(folder) {
         return $http({
            method: 'POST',
            data: folder,
            url: apiUrl + fileApiSection + "/" + folder.parentId
         });
      },
      renameNode: function(forumId,nodeId, name) {
         return $http({
            method: 'PUT',
            params: {
               forumId: forumId,
               nodeName: name
            },
            url: apiUrl + fileApiSection + '/' + nodeId
         });
      },
      deleteForumFileNode: function(forumId, folderId) {
         return $http({
            method: 'DELETE',
            url: apiUrl + fileApiSection + '/forum/' + forumId + '/node/' + folderId
         });
      },
      getForumFile: function(fileId) {
         // For Now...
         window.open(apiUrl + fileApiSection + '/file/' + fileId, '_blank', '');
         //         return $http({
         //            method: 'GET',
         //            url: apiUrl + apiSection + '/file/' + fileId
         //         }); 
      },
   };
} ]);
