/*
 * This service is going to be the catch-all for all application wide functionality like showing
 * error messages and providing framework level constants
 */
angular.module('services.ImpulseService', [ 'dialogs.main' ]).factory(
		'impulseService', [ 'dialogs', function(dialogs) {

			/*
			 	Enrollment status codes: 
				   Invited = 'I'; // User sent invite receipt not confirmed
				   Rejected = 'R'; // System rejected invite of user
				   Pending = 'P'; // Invite receipt confirmed
				   Declined = 'D'; // Invitee declined invite
				   Accepted = 'A'; // Invitee accepted invite
				   Joined = 'J'; // Invitee is now a member of forum
   				   Suspended = 'S'; // Forum membership suspended
			 */
			var enrollmentStatusMap = {'J': 'Joined', 'A': 'Accepted', 'I': 'Invited',
					'R': 'Rejected', 'P': 'Pending', 'D': 'Declined', 'S': 'Suspended'};
			
			// Return the service object functions
			return {
				enrollmentString: function(statusCode){
					return enrollmentStatusMap[statusCode];
				},
				showError : function(msg, details) {
					dialogs.error(msg, details, {size : 'md'});
				}
			};
} ])

.constant('LOGIN_EVENTS', {
	LOGIN_SUCCESS : 'auth-login-success',
	LOGIN_FAILED : 'auth-login-failed',
	LOGOUT_SUCCESS : 'auth-logout-success',
	SESSION_TIMEOUT : 'auth-session-timeout',
	NOT_AUTHENTICATION : 'auth-not-authenticated',
	NOT_AUTHORIZED : 'auth-not-authorized'
})
;
