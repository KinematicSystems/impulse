/*
 * NOTE: You may notice that postModel is used rather than post.title and post.content in the scope.
 * This is because the isolated scope will not bind changes to primative types like a 
 * string back up to the parent so by using an object the problem is fixed.  I suppose you could also not
 * create an isolated scope. Even though we are not using transclusion in this case the link 
 * below provides more detailed information (mattg)
 * http://stackoverflow.com/questions/14481610/two-way-binding-not-working-in-directive-with-transcluded-scope
 * 
 */
angular.module('workspaceDirectives').directive('postEdit', [ '$http', '$document', function($http, $document) {
   return {
      restrict: 'A',
      transclude: false,
      scope: {
         postModel: "=",
         isDirty: "="
      },
      controller: function($scope) {
         $http({
            method: 'GET',
            url: '../api/posting-templates'
         }).success(function(data, status) {
            $scope.postTemplates = data;
         });

//         $scope.$watch("postEditForm.postTitle.$dirty", function(newValue) {
//            if (newValue)
//            {
//               $scope.isDirty = $scope.isDirty || newValue;
//            }
//         });

         $scope.insertTemplate = function(templateIndex) {
            // alert("insert template clicked");
            //var sampleTemplate = "<h2>This is some sample text:</h2><p><b>Bold &nbsp;</b><i>Italics &nbsp;</i><u>Underline</u></p>";
            //$scope.postModel.content += sampleTemplate;
            $scope.postModel.content += $scope.postTemplates[templateIndex].content;
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