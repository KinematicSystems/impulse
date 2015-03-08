/*
 * NOTE: You may notice that postModel is used rather than post.title and post.content in the scope.
 * This is because the isolated scope will not bind changes to primative types like a 
 * string back up to the parent so by using an object the problem is fixed.  I suppose you could also not
 * create an isolated scope. Even though we are not using transclusion in this case the link 
 * below provides more detailed information (mattg)
 * http://stackoverflow.com/questions/14481610/two-way-binding-not-working-in-directive-with-transcluded-scope
 * 
 */
angular.module('workspaceDirectives').directive('postEdit', [ '$document', function($document) {
   return {
      restrict: 'A',
      transclude: false,
      scope: {
         postModel: "="
      },
      controller: function($scope) {
         function XXX() {
         }

         $scope.YYY = function() {
         };
      },
      templateUrl: 'directives/postedit/postedit.html',
      //templateUrl: 'templates/postedit/postedit.html',
      link: function(scope, element, attrs) {
      }
   };
} ])

.run([ '$templateCache', function($templateCache) {
   'use strict';
   // Not used
   $templateCache.put('templates/postedit/postedit.html', "<div>POST EDITOR</div>\n");

} ]);