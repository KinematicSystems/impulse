describe("Date Link Directive", function() {
   var testElement, node;
   var html = '<div><span date-link date-value="testDate"></span></div>';

   beforeEach(module('impulseFilters'));
   beforeEach(module('workspaceDirectives'));

   beforeEach(inject(function(_$compile_, _$rootScope_) {
      // The injector unwraps the underscores (_) from around the parameter names when matching
      var $compile = _$compile_;
      var scope = _$rootScope_;
      scope.testDate = '2014-12-11 15:32:51';

      // need a wrapping element <div> because this directive uses transclusion
      node = $compile(html)(scope);
      scope.$digest();
      testElement = angular.element(node[0].childNodes[0]);
   }));

   it('should produce an element containing a date link element', function() {
      expect(node[0].childNodes.length).toEqual(1);
   });

   it('has a first child that is a span', function() {
      var element = node[0].childNodes[0];
      expect(element.tagName).toEqual("SPAN");
      //expect(node.find('a').length).toEqual(1);
   });
   
   it('has an <a>nchor tag', function() {
      var element = testElement[0].childNodes[0];
      expect(element.tagName).toEqual("A");
      //expect(element.childNodes[1].tagName).toEqual("SPAN");
      //expect(node.find('a').length).toEqual(1);
   });

   it('has a span with the clock marker icon', function() {
      var anchor = testElement[0].childNodes[0];
      expect(anchor.childNodes[0].className).toEqual("fa fa-clock-o");
   });

   it('has no HREF specified in the anchor', function() {
     var anchor = testElement[0].childNodes[0];
     expect(anchor.attributes['href'].baseURI).toEqual(null);
   });

   it('has a linkClicked hander', function() {
     var anchor = testElement[0].childNodes[0];
     expect(anchor.attributes['ng-click'].nodeValue).toEqual("linkClicked()");
   });

   it('has properly set source date (dateVal)', function() {
     isolatedScope = testElement.isolateScope();
     expect(isolatedScope.dateVal).toEqual("2014-12-11 15:32:51");
   });
   
   it('has a span with the properly formatted date text', function() {
     var anchor = testElement[0].childNodes[0];
     expect(anchor.childNodes[1].innerText).toEqual("Dec 11, 2014 at 3:32 PM");
   });
});
