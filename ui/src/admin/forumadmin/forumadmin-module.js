angular.module('forumAdminModule', ['services.ForumAdminService', 'services.ImpulseService', 'ngTable'])

.controller('ForumAdminController', ['$scope',
                            		'$filter',
                            		'forumAdminService',
                            		'impulseService',
                            		'ngTableParams',
		function($scope, $filter, forumAdminService, impulseService, ngTableParams) 
		{
			$scope.enrollmentList = [];
			$scope.groupBy = 'forumName'; 

			/* jshint ignore:start */
			$scope.tableParams = new ngTableParams({
				page : 1, // show first page
				count : 200, // count per page
				sorting : {
					forumName : "asc" // initial sorting
				}
			}, {
				groupBy: function(item) {
					if ($scope.groupBy === 'lastName')
					{	
						return item.firstName + ' ' + item.lastName + ' (' + item.userId + ')';
					}
					else if ($scope.groupBy === 'enrollmentStatus')
					{
						return impulseService.enrollmentString(item.enrollmentStatus);
					}
					else
					{
						return item[$scope.groupBy];
					}
		        },
				counts : [], // hide page counts control
				total : 1, // value less than count hide pagination

				getData : function($defer, params) {
					forumAdminService.getAllForumEnrollment().success(function(data, status) {
						$scope.enrollmentList = $filter('orderBy')(data,$scope.groupBy);
						params.total(data.length);
						$defer.resolve($scope.enrollmentList);
					});
				}
			});
			/* jshint ignore:end */
			
		    $scope.$watch('groupBy', function(value) {
	            //$scope.tableParams.settings().groupBy = value;
	            $scope.tableParams.reload();
	        });
		    
			$scope.enrollmentString = function(statusCode)
			{
				return impulseService.enrollmentString(statusCode);
			};
		} 
]);
