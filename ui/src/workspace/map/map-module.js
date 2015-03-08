angular.module('mapModule', [ 'angular-inview', 'services.MapService', 'leaflet-directive', 'services.ImpulseService' ])

.controller(
      'MapController',
      [ '$scope', '$rootScope', 'LOGIN_EVENTS', 'APP_EVENTS', 'mapService', 'leafletData',
            function($scope, $rootScope, LOGIN_EVENTS, APP_EVENTS, mapService, leafletData) {
               $scope.markers = [];
         
               // Map Config
               angular.extend($scope, {
                  berlin: {
                     lat: 52.52,
                     lng: 13.40,
                     zoom: 14
                  },
                  london: {
                     lat: 51.505,
                     lng: -0.09,
                     zoom: 11
                  },
                  newark: {
                     lat: 40.7336,
                     lng: -74.1711,
                     zoom: 11
                  },
                  layers: {
                     baselayers: {
                        googleTerrain: {
                           name: 'Google Terrain',
                           layerType: 'TERRAIN',
                           type: 'google'
                        },
                        googleHybrid: {
                           name: 'Google Hybrid',
                           layerType: 'HYBRID',
                           type: 'google'
                        },
                        googleRoadmap: {
                           name: 'Google Streets',
                           layerType: 'ROADMAP',
                           type: 'google'
                        }
                     }
                  },
                  defaults: {
                     scrollWheelZoom: false
                  }
               });

               $scope.$on(LOGIN_EVENTS.LOGIN_SUCCESS, function(event, params) {
                  var userId = params.userId;
                  $scope.pageTitle = 'Map for ' + userId;
               });

               $scope.$on(LOGIN_EVENTS.LOGOUT_SUCCESS, function(event, params) {
                  $scope.pageTitle = 'Map (not logged in)';
               });

               $scope.isInView = function(index, inview, inviewpart) {
//                  var inViewReport = inview ? 'enter: ' : 'exit: ';
//                  if (typeof(inviewpart) != 'undefined') {
//                     inViewReport += inviewpart;
//                  }
//                  console.log("angular-inview " + inViewReport);
                  $scope.redrawMap();
               };
               
               $scope.$on(APP_EVENTS.MAP.GOTO_LOCATION, function(event, params) {
                  //alert("Map recieved GOTO_LOCATION @" + location);
                  $rootScope.$broadcast(APP_EVENTS.ACTIVATE_MODULE, "map");
                  $scope.leafMap.panTo(params);
                  $scope.markers.push({
                     lat: params.lat,
                     lng: params.lng,
                     message: params.description
                 });
              });

               // Store the leaflet map
               leafletData.getMap().then(function(map) {
                  $scope.leafMap = map;
               });

               $scope.redrawMap = function() {
                 // $('#leafMap').css("height", ($('#mapContainer').height()));    
                  $scope.leafMap.invalidateSize(false);
               };

            } ]);
