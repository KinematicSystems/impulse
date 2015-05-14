/* BASICALLY IMHO THIS IS A BUST.
 * Angular JS expects you to use a plugin for karma in order to test directives that load
 * their html template from files. Not ready for that yet.
 */
describe("Enrollment List Directive Tests", function() {
   var $httpBackend, testElement, node;
   var html = '<div><div enrollment-list enrollment-status="testStatus"></div>';

   beforeEach(module('impulseFilters'));
   beforeEach(module('workspaceDirectives'));

   beforeEach(inject(function($injector, _$compile_, _$rootScope_) {
      // The injector unwraps the underscores (_) from around the parameter names when matching
      var $compile = _$compile_;
      var scope = _$rootScope_;
      scope.testStatus = 'I';

      // Mock the forum service calls
      $httpBackend = $injector.get('$httpBackend');
      // backend definition common for all tests
      var httpHandler = $httpBackend.when('GET', 'directives/enrollmentlist/enrollmentlist.html')
                             .respond({userId: 'userX'}, {'A-Token': 'xxx'});
     
      
      // need a wrapping element <div> because this directive uses transclusion
      node = $compile(html)(scope);
      scope.$digest();
      testElement = angular.element(node[0].childNodes[0]);
   }));

   it('should produce an element containing a enrollment list element', function() {
      expect(node[0].childNodes.length).toEqual(1);
   });

});
