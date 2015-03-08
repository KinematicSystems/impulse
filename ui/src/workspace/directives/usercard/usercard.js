angular.module('workspaceDirectives').directive('userCard', [ '$document', function($document) {
   return {
      restrict: 'A',
      transclude: true,
      scope: {
         userModel: '='
      },
      controller: function($scope) {
         function XXX() {
         }

         $scope.YYY = function() {
         };
      },
      templateUrl: 'directives/usercard/usercard.html',
      //templateUrl: 'templates/usercard/usercard.htm',
      link: function(scope, element, attrs) {
      }
   };
} ])

.run([ '$templateCache', function($templateCache) {
   'use strict';
   // Not used
   $templateCache.put('templates/usercard/usercard.html', "<div>{{userModel}}</span>\n");

} ]);