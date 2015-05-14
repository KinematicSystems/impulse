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


