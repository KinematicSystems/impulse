angular.module('impulseFilters', [])

.filter('formatDbDate', [ '$filter', function($filter) {
   var dateFilter = $filter('date');
 //  var formatStr = "EEEE, MMMM d, y 'at' h:mm a"; // Thursday, November 13, 2014 at 4:34 PM
   var formatStr = "MMM d, y 'at' h:mm a"; // Sep 3, 2010 at 4:34 PM

   return function(dateStr) {
      if (typeof(dateStr) === undefined)
      {
         return;
      }
      dateStr = dateStr.split(' ').join('T'); 
      return dateFilter(dateStr, formatStr);
   };
}]);
