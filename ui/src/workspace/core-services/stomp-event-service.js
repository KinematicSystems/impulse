/*
 * This service connects to stomp and subscribes to the topics, then broadcasts events to listeners. 
 */
angular.module('services.EventService', [ 'services.ImpulseService', 'services.ForumService' ]).factory('eventService',
      [ '$rootScope', '$filter', 'forumService', 'ENROLLMENT_STATUS', function($rootScope, $filter, forumService, ENROLLMENT_STATUS) {
         var client = null;
         var url = "ws://localhost:61623";
         var login = "admin";
         var passcode = "password";
         var debugMode = false;

         /*
          * A quick explaination of the eventing (mattg)
          * There is a topic for this user.
          * There is a topic for each forum this user is in.
          * There will probably be general topics too.
          * The actual listeners are more general than the topic i.e. any message intended for user 'joex'
          * will come in on 'topic/joex' but the type will be used for eventing ('USER_INVITE', 'USER_MESSAGE')
          */
         function onStompUserEvent(event) {
            client.debug("Stomp User Event: " + event);
            var content = JSON.parse(event.body);
            $rootScope.$broadcast(content.type, event.headers.sourceUserId, content);
         }

         function onStompForumEvent(event) {
            var content = JSON.parse(event.body);
            $rootScope.$broadcast(content.type, event.headers.sourceUserId, content);
         }

         function isConnected() {
            return (client && client.isConnected);
         }

         // Return the service object functions
         return {
            init: function(params) {
               debugMode = params.debugMode;

               client = Stomp.client(url);

               // this allows to display debug logs directly on the web page
               client.debug = function(str) {
                  if (debugMode)
                  {
                     console.log(str);
                  }
               };

               client.isConnected = false;
            },
            connect: function(userId) {
               if (isConnected())
               {
                  return;
               }

               // the client is notified when it is connected to the server.
               client.connect(login, passcode, function(frame) {
                  client.isConnected = true;
                  client.debug("connected to Stomp");
                  client.subscribe('/topic/' + userId, onStompUserEvent);
                  forumService.getJoinedForums().success(function(data, status) {
                     // Filter list for only Joined status
                     var forumList = data;
                     for (var i = 0; i < forumList.length; ++i)
                     {
                        client.subscribe('/topic/' + forumList[i].id, onStompForumEvent);
                     }
                  });
               });
            },
            //
            //      connect: function() {
            //         var deferred = $q.defer();
            //
            //         // the client is notified when it is connected to the server.
            //         client.connect(login, passcode, function(frame) {
            //            client.isConnected = true;
            //            client.debug("connected to Stomp");
            //            deferred.resolve();
            //         });
            //
            //         return deferred.promise;
            //      },
            setPolling: function(userId, bPoll) {
               // NOP
            },

            disconnect: function(userId) {
               client.disconnect(function() {
                  client.debug("Stomp disconnected");
               });
            },

            synchronize: function() {
               // NOP
             }

// AS Per Design client should not be directly sending events
//            send: function(destination, msgText, headers) {
//               if (typeof (headers) !== undefined)
//               {
//                  client.send(destination, headers, msgText);
//               }
//               else
//               {
//                  client.send(destination, {}, msgText);
//               }
//            }
         };
      } ]);
