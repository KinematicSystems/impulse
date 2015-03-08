/*
 * This service is going to be the catch-all for all application wide functionality like showing
 * error messages and providing framework level constants
 */
angular.module('services.ImpulseService', [ 'dialogs.main' ]).factory('impulseService', [ 'dialogs', function(dialogs) {

   /*
    	Enrollment status codes: 
   	   Invited = 'I'; // User sent invite receipt not confirmed
   	   Rejected = 'R'; // System rejected invite of user
   	   Pending = 'P'; // Invite receipt confirmed
   	   Declined = 'D'; // Invitee declined invite
   	   Accepted = 'A'; // Invitee accepted invite
   	   Joined = 'J'; // Invitee is now a member of forum
   		Suspended = 'S'; // Forum membership suspended
   		Left = 'L'; // User Left Forum
    */
   var enrollmentStatusMap = {
      'J': 'Joined',
      'A': 'Accepted',
      'I': 'Invited',
      'R': 'Rejected',
      'P': 'Pending',
      'D': 'Declined',
      'S': 'Suspended',
      'L': 'Left'
   };

   var currentUser = null;
   var collaborator = false;

   // Return the service object functions
   return {
      enrollmentString: function(statusCode) {
         return enrollmentStatusMap[statusCode];
      },
      setCurrentUser: function(currUser) {
         currentUser = currUser;
      },
      getCurrentUser: function() {
         return currentUser;
      },
      setCollaborator: function(collab) {
         collaborator = collab;
      },
      isCollaborator: function() {
         return collaborator;
      },
      showError: function(heading, msg) {
         dialogs.error(heading, msg, {
            size: 'sm'
         });
      },
      showNotification: function(heading, msg) {
         dialogs.notify(heading, msg, {
            size: 'sm'
         });
      },
      showPrompt: function(heading, label, msg, defaultValue) {
         var params = {
            heading: heading,
            label: label,
            msg: msg,
            defaultValue: defaultValue
         };
         return dialogs.create('../common/impulse/prompt-dialog.html', 'promptDialogCtrl', params, {
            size: 'sm'
         });
      },
      showConfirm: function(heading, msg) {
         return dialogs.confirm(heading, msg, {
            size: 'sm'
         });
      }
   };
} ])

.controller('promptDialogCtrl', function($scope, $modalInstance, data) {
   $scope.promptValue = data.defaultValue;
   $scope.promptLabel = data.label;
   $scope.promptMessage = data.msg;
   $scope.promptHeading = data.heading;

   $scope.cancel = function() {
      $modalInstance.dismiss('Canceled');
   }; // end cancel

   $scope.save = function() {
      $modalInstance.close($scope.promptValue);
   }; // end save

   $scope.hitEnter = function(evt) {
      if (angular.equals(evt.keyCode, 13) && !(angular.equals($scope.promptValue, null) || angular.equals($scope.promptValue, '')))
      {
         $scope.save();
      }
   };
}) // end controller(promptDialogCtrl)

.constant('LOGIN_EVENTS', {
   LOGIN_SUCCESS: 'auth-login-success',
   LOGIN_FAILED: 'auth-login-failed',
   LOGOUT_SUCCESS: 'auth-logout-success',
   SESSION_TIMEOUT: 'auth-session-timeout',
   NOT_AUTHENTICATION: 'auth-not-authenticated',
   NOT_AUTHORIZED: 'auth-not-authorized'
})

.constant('COLLAB_EVENTS', {
   SYNCHRONIZE: 'SYNCHRONIZE',
   MESSAGE: 'MESSAGE',
   EVENT: 'EVENT',
   FORUM: {
      ENROLLMENT: 'FORUM_ENROLLMENT',
      CHANGE: 'FORUM_CHANGE',
      NODE_CHANGE: 'FORUM_NODE_CHANGE',
      CT_CREATE: 'CREATE', // CT = change type
      CT_UPDATE: 'UPDATE',
      CT_DELETE: 'DELETE',
   },
   USER: {
      INVITE: 'USER_INVITE',
      REMOVED: 'USER_REMOVED'
   }
})

.constant('APP_EVENTS', {
   MAP: {
      GOTO_LOCATION: 'MAP_GOTO_LOCATION'
   },
   ACTIVATE_MODULE: 'ACTIVATE_MODULE'
})

.constant('ENROLLMENT_STATUS', {
   JOINED: 'J',
   ACCEPTED: 'A',
   INVITED: 'I',
   REJECTED: 'R',
   PENDING: 'P',
   DECLINED: 'D',
   SUSPENDED: 'S',
   LEFT: 'L'
});
