/*
 * This service connects to the impulse custom event service. 
 */
angular.module('services.EventService', [ 'services.EventService', 'services.ForumService' ]).factory(
      'eventService',
      [ '$http', '$timeout', '$rootScope', '$filter', 'forumService', 'ENROLLMENT_STATUS',
            function($http, $timeout, $rootScope, $filter, forumService, ENROLLMENT_STATUS) {
               var isConnected = false;
               var isPolling = false;
               var apiUrl = '../api/events/';
               var debugMode = false;
               var pollingInterval = 3000;
               var forumList = [];
               /*
                * A quick explaination of the eventing (mattg)
                * There is a topic for this user.
                * There is a topic for each forum this user is in.
                * There will probably be general topics too.
                * The actual listeners are more general than the topic i.e. any message intended for user 'joex'
                * will come in on 'topic/joex' but the type will be used for eventing ('USER_INVITE', 'USER_MESSAGE')
                */
               function onUserEvent(event) {
                  var content = JSON.parse(event.content);
                  $rootScope.$broadcast(content.type, event.sourceUserId, content);
               }

               function onForumEvent(event) {
                  var content = JSON.parse(event.content);
                  $rootScope.$broadcast(content.type, event.sourceUserId, content);
               }

               function logDebug(msg) {
                  if (debugMode)
                  {
                     console.log(msg);
                  }
               }

               function subscribe(userId, topic) {
                  return $http({
                     method: 'POST',
                     data: {
                        userId: userId,
                        topic: topic
                     },
                     url: apiUrl + userId + '/' + topic
                  });
               }

               function unsubscribe(userId, topic) {
                  return $http({
                     method: 'DELETE',
                     url: apiUrl + userId + '/' + topic
                  });
               }

               function pullEvents(userId) {
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
                  });
               }

               var poll = function(userId) {
                  $timeout(function() {
                     if (isConnected && isPolling)
                     {
                        pullEvents(userId);
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
                     subscribe(userId, 'USER.' + userId);
                     forumService.getJoinedForums().success(function(data, status) {
                        // Filter list for only Joined status
                        forumList = data;
                        for (var i = 0; i < forumList.length; ++i)
                        {
                           subscribe(userId, 'FORUM.' + forumList[i].id);
                        }
                     });
                  },

                  setPolling: function(userId, bPoll) {
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
