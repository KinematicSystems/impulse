angular.module('services.EnrollmentService', []).factory('enrollmentService', [ '$http', function($http) {
   var apiUrl = '../api/';
   var apiSection = 'enrollment';

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
      getJoinedForums: function(userId) {
         // This will return a list of forum objects
         return runGetRequest(apiSection + "/" + userId + "/joined");
      },
      getEnrolled: function(userId) {
         // This will return a list of forum enrollment objects
         return runGetRequest(apiSection + "/" + userId + "/enrolled");
      },
      getInvitations: function(userId) {
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + "/" + userId + "/invitations"
         });
      },
      getPendingJoinRequests: function(userId) {
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + "/" + userId + "/pending-join-requests"
         });
      },
      getRejections: function(userId) {
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + "/" + userId + "/rejections"
         });
      },
      getForumUsers: function(forumId) {
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + "/" + forumId + "/users/enrolled"
         });
      },
      getUsersForInvite: function(forumId) {
         return $http({
            method: 'GET',
            url: apiUrl + apiSection + "/" + forumId + "/users/for-invite"
         });
      },
      removeRejection: function(forumId, userId) {
         return $http({
            method: 'DELETE',
            url: apiUrl + apiSection + "/" + forumId + "/user/" + userId
         });
      },
      acceptInvite: function(forumId, userId) {
         return $http({
            method: 'POST',
            url: apiUrl + apiSection + "/" + forumId + "/accept/" + userId
         });
      },
      leaveForum: function(forumId, userId) {
         return $http({
            method: 'POST',
            url: apiUrl + apiSection + "/" + forumId + "/leave/" + userId
         });
      },
      approveJoinRequest: function(forumId, userId) {
         return $http({
            method: 'POST',
            url: apiUrl + apiSection + "/" + forumId + "/approve/" + userId
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
            url: apiUrl + apiSection + "/" + forumId + "/status/" + userId
         });
      }

   };
} ]);
