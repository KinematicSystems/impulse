angular.module('workspaceDirectives')

.directive('locationLink', [ '$rootScope', 'APP_EVENTS', function($rootScope, APP_EVENTS) {
   return {
      restrict: 'A',
      transclude: true,
      scope: {
         lat: '=latitude',
         lng: '=longitude'
      },
      controller: function($scope) {
         $scope.linkClicked = function(latitude, longitude, description) {
            //alert("LoationType: " + locationType + " location: " + location + " clicked!");
            // TODO Use the type to convert to decimal degrees then fire event
            $rootScope.$broadcast(APP_EVENTS.MAP.GOTO_LOCATION, {
               lat: latitude,
               lng: longitude,
               description: description
            });
         };
      },
      templateUrl: 'templates/locationLink/locationLink.html',
      link: function(scope, element, attrs) {
         scope.description = element.context.innerText;
      }
   };
} ])

.run([ '$templateCache', function($templateCache) {
   'use strict';

   $templateCache.put('templates/locationLink/locationLink.html', "<a href=\"\" ng-click=\"linkClicked(lat, lng, description)\" style=\"white-space: nowrap;\"><span class=\"fa fa-map-marker\" style=\"padding-right:2px;\"></span><span ng-transclude></span></a>\n");

} ]);