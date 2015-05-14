// Depends on login.spec.js to have logged in.

var ForumManagerPage = function() {
   //   this.pageURL = 'http://localhost:8888/impulse/workspace/';
   this.heading = element(by.id('managerHeading'));
   this.forumList = element(by.id('managerMyForums')).all(by.repeater("enrollItem in enrollmentList"));
   this.explorerForumList = element(by.id('explorerForumSelectionList')).all(by.repeater("forum in forumList"));
   this.forumNameElement = this.forumList.first().element(by.css('.enrollmentListForumName'));
   this.forumDescriptionElement = this.forumList.first().element(by.css('.enrollmentListDescription'));
   this.dialogTitle = element(by.css('.modal-title'));
   this.editButton = this.forumList.first().element(by.css('.btn-primary'));
   this.deleteButton = this.forumList.first().element(by.css('.btn-danger'));

   //   this.get = function() {
   //      browser.get(this.pageURL);
   //   };

   this.modalDialogOK = function() {
      element(by.css('.modal-footer')).element(by.css('.btn-primary')).click();
   };

   this.modalDialogYES = function() {
      element(by.css('.modal-footer')).element(by.css('.btn-default')).click();
   };

   this.deleteForumClick = function() {
      //browser.waitForAngular();
      this.deleteButton.click();
   };

   this.showManager = function() {
      element(by.id('sidebarManager')).click();
   };

   this.newForumClick = function() {
      element(by.id('managerNewButton')).click();
   };

   this.editForumClick = function() {
      this.editButton.click();
   };

   this.saveForumClick = function() {
      element(by.id('forumSaveButton')).click();
   };

   this.setNameAndDescription = function(name, description) {
      element(by.id('forumName')).clear();
      element(by.id('forumName')).sendKeys(name);
      element(by.id('forumDescription')).clear();
      element(by.id('forumDescription')).sendKeys(description);
   };
};

describe('Forum CRUD', function() {
   var thePage = new ForumManagerPage();
   var FORUM_NAME = 'ForumForE2ETest';

   it('should be logged in', function() {
      //    thePage.get();
      //    expect(browser.getCurrentUrl()).toEqual(thePage.pageURL);
      expect(browser.getTitle()).toEqual('imPulse Workspace');
      var logoutLink = element(by.id('logoutLink'));
      expect(logoutLink.isPresent()).toBe(true);
   });

   it('should navigate to forum manager', function() {
      thePage.showManager();
      expect(thePage.heading.getText()).toMatch('Forum Management');
   });

   it('should create a new forum named: ' + FORUM_NAME, function() {
      thePage.newForumClick();
      thePage.setNameAndDescription(FORUM_NAME, 'E2E Test Forum Description');
      thePage.saveForumClick();
      expect(thePage.forumList.count()).toEqual(1);
      expect(thePage.forumNameElement.getText()).toMatch(FORUM_NAME);
   });

   it('should show a created confirmation dialog', function() {
      expect(thePage.dialogTitle.getText()).toMatch('Forum Created');
      thePage.modalDialogOK();
   });

   it('should have the new forum in the file explorer', function() {
      expect(thePage.explorerForumList.count()).toEqual(1);
   });

   it('should edit the forum description', function() {
      thePage.editForumClick();
      thePage.setNameAndDescription(FORUM_NAME, 'Changed Description');
      thePage.saveForumClick();
      expect(thePage.forumDescriptionElement.getText()).toMatch('Changed Description');
   });

   it('should show an update confirmation dialog', function() {
      expect(thePage.dialogTitle.getText()).toMatch('Forum Updated');
      thePage.modalDialogOK();
   });

   it('should delete the forum named: ' + FORUM_NAME, function() {
      expect(thePage.deleteButton.getText()).toMatch('delete');

      thePage.deleteForumClick();
      // Make sure confirm dialog is displayed
      expect(thePage.dialogTitle.getText()).toMatch('Confirm Delete');
      thePage.modalDialogYES();
      expect(thePage.dialogTitle.getText()).toMatch('Forum Deleted');
      thePage.modalDialogOK();
      expect(thePage.explorerForumList.count()).toEqual(0);
      expect(thePage.forumList.count()).toEqual(0);
   });

});
