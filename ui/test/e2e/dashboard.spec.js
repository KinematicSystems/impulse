// Depends on login.spec.js to have logged in.

var DashboardPage = function() {
   //   this.pageURL = 'http://localhost:8888/impulse/workspace/';
   this.heading = element(by.id('dashboardHeading'));

   //   this.get = function() {
   //      browser.get(this.pageURL);
   //   };

   this.showDashboard = function() {
      element(by.id('sidebarDashboard')).click();
   };
};

describe('Dashboard', function() {
   var thePage = new DashboardPage();

   it('should be logged in', function() {
      //     thePage.get();
      //    expect(browser.getCurrentUrl()).toEqual(thePage.pageURL);
      expect(browser.getTitle()).toEqual('imPulse Workspace');
      var logoutLink = element(by.id('logoutLink'));
      expect(logoutLink.isPresent()).toBe(true);
   });

   it('should navigate to dashboard', function() {
      thePage.showDashboard();
      expect(thePage.heading.getText()).toMatch('Dashboard');
   });

});
