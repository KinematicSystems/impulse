var LoginPage = function() {
   this.pageURL = 'http://localhost:8888/impulse/workspace/';
   this.userIdField = element(by.model('userId'));
   this.passwordField = element(by.model('password'));
   this.loginButton = element(by.id('loginButton'));
   this.loginError = element(by.id('loginErrorMsg'));

   this.setUserId = function(userId) {
      this.userIdField.clear();
      this.userIdField.sendKeys(userId);
   };

   this.setPassword = function(password) {
      this.passwordField.clear();
      this.passwordField.sendKeys(password);
   };

   this.get = function() {
      browser.get(this.pageURL);
   };

   this.submit = function() {
      this.loginButton.click();
   }
};

describe('Login Logout', function() {
   var thePage = new LoginPage();
   var uid = 'testuser0';
   var pwd = 'change.me';
/* Quick   
   it('should redirect', function() {
      thePage.get();
      expect(browser.getTitle()).toEqual('imPulse Workspace');
      expect(browser.getCurrentUrl()).toEqual(thePage.pageURL);
   });

   it('should fail login attempt', function() {
      thePage.setUserId(uid);
      thePage.setPassword('wrong-password');
      thePage.submit();

      expect(thePage.loginError.getText()).toMatch('User ID/Password combination is invalid.');
    });

   it('should login', function() {
      thePage.setUserId(uid);
      thePage.setPassword(pwd);
      thePage.submit();
      // logout link is on the workspace page 
      var logoutLink = element(by.id('logoutLink'));
      expect(logoutLink.isPresent()).toBe(true);
   });

   it('should logout', function() {
      var logoutLink = element(by.id('logoutLink'));
      expect(logoutLink.isPresent()).toBe(true);
      logoutLink.click();
      expect(logoutLink.isPresent()).toBe(false);
   });
*/
   // Log in again and leave logged in for next test
   it('should login again', function() {
      thePage.get();
      thePage.setUserId(uid);
      thePage.setPassword(pwd);
      thePage.submit();
      expect(element(by.css('.loginBlock')).isPresent()).toBe(true);
      var logoutLink = element(by.id('logoutLink'));
      expect(logoutLink.isPresent()).toBe(true);
   });
});


