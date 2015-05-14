/*
 * This service connects to the impulse custom event service. 
 */
angular.module('services.EventService', [ 'services.EventService', 'services.EnrollmentService' ]).factory(
      'eventService',
      [ '$http', '$timeout', '$rootScope', '$filter', 'enrollmentService', 'ENROLLMENT_STATUS',
            function($http, $timeout, $rootScope, $filter, enrollmentService, ENROLLMENT_STATUS) {
               var isConnected = false;
               var isPolling = false;
               var apiUrl = '../api/events/';
               var debugMode = true;
               //var forumList = [];

               /*
                * Polling Interval Management
                * 
                * Poll at pollingInterval for POLL_CYCLES then increment by POLL_INCREMENT
                * If an event occurs reset pollingInterval and pollingCycle
                * 
                */
               var POLL_MIN_INTERVAL = 2000;
               var POLL_MAX_INTERVAL = 10000;
               var POLL_INCREMENT = 2000;
               var POLL_CYCLES = 3;

               var pollingInterval = POLL_MIN_INTERVAL;
               var pollingCycle = 0;

               function resetPolling() {
                  // Reset Polling
                  pollingInterval = POLL_MIN_INTERVAL;
                  pollingCycle = 0;
               }

               function updatePolling() {
                  pollingCycle++;
                  if (pollingCycle === POLL_CYCLES)
                  {
                     pollingCycle = 0;
                     if (pollingInterval < POLL_MAX_INTERVAL)
                     {
                        pollingInterval += POLL_INCREMENT;
                     }
                  }
               }

               /*
                * A quick explaination of the eventing (mattg)
                * There is a topic for this user.
                * There is a topic for each forum this user is in.
                * There will probably be general topics too.
                * The actual listeners are more general than the topic i.e. any message intended for user 'joex'
                * will come in on 'topic/joex' but the type will be used for eventing ('USER_INVITE', 'USER_MESSAGE')
                */
               function onUserEvent(event) {
                  resetPolling();
                  var content = JSON.parse(event.content);
                  $rootScope.$broadcast(content.type, event.sourceUserId, content);

               }

               function onForumEvent(event) {
                  resetPolling();
                  var content = JSON.parse(event.content);
                  $rootScope.$broadcast(content.type, event.sourceUserId, content);
               }

               function logDebug(msg) {
                  if (debugMode)
                  {
                     console.log(msg);
                  }
               }

               function initSubscriptions(userId) {
                  return $http({
                     method: 'POST',
                     data: {
                        userId: userId
                     },
                     url: apiUrl + userId + '/subscribe'
                  });
               }

               function subscribeForum(userId, forumId) {
                  var topic = "FORUM." + forumId;
                  return $http({
                     method: 'POST',
                     data: {
                        userId: userId,
                        topic: topic
                     },
                     url: apiUrl + userId + '/' + topic
                  });
               }

               function unsubscribeForum(userId, forumId) {
                  var topic = "FORUM." + forumId;
                  return $http({
                     method: 'DELETE',
                     url: apiUrl + userId + '/' + topic
                  });
               }

               function pullEvents(userId) {
                  if (isConnected)
                  {
                     $http({
                        method: 'GET',
                        url: apiUrl + userId
                     }).success(function(data, status) {
                        for (var i = 0; i < data.length; ++i)
                        {
                           var event = data[i];

                           if (event.topic.indexOf('USER.') === 0)
                           {
                              onUserEvent(event);
                              logDebug(event);
                           }
                           else if (event.topic.indexOf('FORUM.') === 0)
                           {
                              onForumEvent(event);
                              logDebug(event);
                           }
                        }
                     }).error(function(data, status) {
                        isConnected = false;
                        isPolling = false;
                     });
                  }
               }

               var poll = function(userId) {
                  pullEvents(userId);
                  $timeout(function() {
                     if (isPolling)
                     {
                        updatePolling();
                        d = new Date();
                        //logDebug("Polling at " + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds());
                        poll(userId);
                     }
                  }, pollingInterval);
               };

               // Return the service object functions
               return {
                  init: function(params) {
                     debugMode = params.debugMode;
                     isConnected = false;
                  },
                  connect: function(userId) {
                     if (isConnected)
                     {
                        return;
                     }

                     isConnected = true;
                     logDebug("Connected to imPulse event service");
                     initSubscriptions(userId);
                  },

                  subscribeToForum: function(userId, forumId) {
                     subscribeForum(userId,forumId);
                   },

                  unsubscribeFromForum: function(userId, forumId) {
                     unsubscribeForum(userId,forumId);
                  },
                  
                  setPolling: function(userId, bPoll) {
                     resetPolling();
                     isPolling = bPoll;
                     if (isPolling)
                     {
                        poll(userId);
                     }
                  },
                  disconnect: function(userId) {
                     isConnected = false;
                     isPolling = false;
                     // The server will kill all the subscriptions/events on logout
                     //                     unsubscribe(userId, 'USER.' + userId);
                     //                     for (var i = 0; i < forumList.length; ++i)
                     //                     {
                     //                        unsubscribe(userId,'FORUM.' + forumList[i].id);
                     //                     }
                  },

                  synchronize: function(userId) {
                     pullEvents(userId);
                  }
               };
            } ]);
