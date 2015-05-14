/*
 * This service connects to stomp and subscribes to the topics, then broadcasts events to listeners. 
 */
angular.module('services.EventService', [ 'services.ImpulseService', 'services.EnrollmentService' ]).factory(
      'eventService',
      [ '$rootScope', '$filter', '$location', 'enrollmentService', 'ENROLLMENT_STATUS',
            function($rootScope, $filter, $location, enrollmentService, ENROLLMENT_STATUS) {
               var client = null;
               var socketPort = "61623";

               var login = "admin";
               var passcode = "password";
               var debugMode = true;
               var forumList = [];
               var TOPIC_PREFIX = "/topic/";
               
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
                  $rootScope.$broadcast(content.type, content.sourceUserId, content);
               }

               function onStompForumEvent(event) {
                  var content = JSON.parse(event.body);
                  $rootScope.$broadcast(content.type, content.sourceUserId, content);
               }

               function createClient() {
                  if (client === null)
                  {
                     var url = "ws://" + $location.host() + ":" + socketPort;
                     var  protocols = ['v10.stomp', 'v11.stomp'];
                     var ws = new WebSocket(url, protocols);

                     client = Stomp.over(ws);
                     client.heartbeat.incoming = 30000; // 30 seconds
                     client.heartbeat.outgoing = 30000; // 30 seconds
                     // this allows to display debug logs directly on the web page
                     client.debug = function(str) {
                        if (debugMode)
                        {
                           console.log(str);
                        }
                     };

                     client.isConnected = false;
                  }
               }

               function connectClient(userId) {
                  if (isConnected())
                  {
                     return;
                  }
                  createClient();

                  // the client is notified when it is connected to the server.
                  client.connect(login, passcode, function(frame) {
                     client.isConnected = true;
                     client.debug("connected to Stomp");
                     client.subscribe(TOPIC_PREFIX + 'USER.' + userId, onStompUserEvent);
                     enrollmentService.getJoinedForums().success(function(data, status) {
                        // Filter list for only Joined status
                        forumList = data;
                        for (var i = 0; i < forumList.length; ++i)
                        {
                           client.subscribe(TOPIC_PREFIX + 'FORUM.' + forumList[i].id, onStompForumEvent);
                        }
                     });
                  });
               }

               function disconnectClient(userId) {
                  if (isConnected())
                  {
                     client.disconnect(function() {
                        client.debug("Stomp disconnected");
                        client = null;
                     });
                  }
               }

               function isConnected() {
                  return (client && client.isConnected);
               }

               // Return the service object functions
               return {
                  init: function(params) {
                     debugMode = params.debugMode;
                  },
                  connect: function(userId) {
                     connectClient(userId);
                  },

                  subscribeToForum: function(userId, forumId) {
                     if (!isConnected())
                     {
                        return;
                     }

                     client.subscribe(TOPIC_PREFIX + 'FORUM.' + forumId, onStompForumEvent);
                  },

                  unsubscribeFromForum: function(userId, forumId) {
                     if (!isConnected())
                     {
                        return;
                     }

                     client.unsubscribe(TOPIC_PREFIX + 'FORUM.' + forumId);
                  },
                  setPolling: function(userId, bPoll) {
                     // NOP 
                     // set for bouncing of web sockets for now
                     if (bPoll)
                     {
                        connectClient(userId);
                     }
                     else
                     {
                        disconnectClient(userId);
                     }
                  },

                  disconnect: function(userId) {
                     disconnectClient(userId);
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
