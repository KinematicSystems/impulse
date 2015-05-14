describe("Location Link Directive", function() {
   var $compile, $rootScope, testElement, node;
   var html = '<div><span location-link latitude="45.678" longitude="-12.345">TEST INNER TEXT</span></div>';
   // Load the myApp module, which contains the directive
   beforeEach(module('workspaceDirectives'));

   // Store references to $rootScope and $compile
   // so they are available to all tests in this describe block
   beforeEach(inject(function(_$compile_, _$rootScope_) {
      // The injector unwraps the underscores (_) from around the parameter names when matching
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      // need a wrapping element <div> because this directive uses transclusion
      node = $compile(html)($rootScope);
      $rootScope.$digest();
      testElement = angular.element(node[0].childNodes[0]);
   }));

   it('should produce an element containing a location link element', function() {
      expect(node[0].childNodes.length).toEqual(1);
   });

   it('has a first child that is a span', function() {
      var element = node[0].childNodes[0];
      expect(element.tagName).toEqual("SPAN");
      //expect(node.find('a').length).toEqual(1);
   });

   it('has a parent span should have two children', function() {
      var element = node[0].childNodes[0];
      expect(element.childNodes.length).toEqual(2);
   });

   it('has an <a>nchor tag', function() {
      var element = testElement[0].childNodes[0];
      expect(element.tagName).toEqual("A");
      //expect(element.childNodes[1].tagName).toEqual("SPAN");
      //expect(node.find('a').length).toEqual(1);
   });

   it('has a span with the map marker icon', function() {
      var anchor = testElement[0].childNodes[0];
      expect(anchor.childNodes[0].className).toEqual("fa fa-map-marker");
   });

   it('has a span with the proper transcluded text', function() {
      var anchor = testElement[0].childNodes[0];
      expect(anchor.childNodes[1].innerText).toEqual("TEST INNER TEXT");
   });

   it('has no HREF specified in the anchor', function() {
      var anchor = testElement[0].childNodes[0];
      expect(anchor.attributes['href'].baseURI).toEqual(null);
   });

   it('has a linkClicked hander', function() {
      var anchor = testElement[0].childNodes[0];
      expect(anchor.attributes['ng-click'].nodeValue).toEqual("linkClicked(lat, lng, description)");
   });

   it('has properly set latitude', function() {
      isolatedScope = testElement.isolateScope();
      expect(isolatedScope.lat).toEqual(45.678);
   });

   it('has properly set longitude', function() {
      isolatedScope = testElement.isolateScope();
      expect(isolatedScope.lng).toEqual(-12.345);
   });
   
});
