angular.module('workspaceDirectives')

.directive('dateLink', [ 'APP_EVENTS', function(APP_EVENTS) {
   return {
      restrict: 'A',
      transclude: false,
      scope: {
         dateVal: '=dateValue'
      },
      controller: function($scope) {
         $scope.linkClicked = function() {
            alert("Date clicked: " + $scope.dateVal);
         };
      },
      templateUrl: 'templates/dateLink/dateLink.html',
      link: function(scope, element, attrs) {
      }
   };
} ])

.run([ '$templateCache', function($templateCache) {
   'use strict';

   $templateCache.put('templates/dateLink/dateLink.html', "<a href=\"\" ng-click=\"linkClicked()\" style=\"white-space: nowrap;\"><span class=\"fa fa-clock-o\" style=\"padding-right:3px;\"></span><span>{{dateVal | formatDbDate}}</span></a>\n");

} ]);