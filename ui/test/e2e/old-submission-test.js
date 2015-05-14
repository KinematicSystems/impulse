var SubmissionPage = function() {
   	this.pageURL = 'https://localhost:8443/impulse/icw-lite/#/submission';
	this.submissionTable = element(by.id('submissionListBody'));;
	this.selectedSite = element(by.model('selectedSite'));
	this.selectedResponse = element(by.model('selectedResponse'));
	this.pagination = element(by.css('.pagination'));

	
	this.get = function() {
		browser.get(this.pageURL);
  	};
  	
  	this.selectSiteByIndex = function(index) {
		element.all(by.css('select[ng-model="selectedSite"] option')).get(index).click();
  	};
  	
  	this.selectResponseByIndex = function(index) {
		element.all(by.css('select[ng-model="selectedResponse"] option')).get(index).click();
  	};
  	
  	this.tableRowCount = function() {
		return this.submissionTable.all(by.tagName('tr')).count();
  	};

  	this.pageCount = function() {
   		return this.pagination.all(by.tagName('li')).count();
 	};
  	
   	this.forAllTableRowsAtColumn = function(colIndex, callback) { // signature of callback is func(columnElement)
		this.submissionTable.all(by.tagName('tr')).then(function(elements) {
  			for (var i=0; i < elements.length; ++i)
  			{
  				var responseCol = elements[i].all(by.tagName('td')).get(4);
  				callback(responseCol);
  			}
		});
   	};
 	
};



describe('Submission page', function() {

	var thePage = new SubmissionPage();
	
	// Submission Page Tests
	it('should be on submission page', function() {
		thePage.get();
		expect(browser.getCurrentUrl()).toEqual(thePage.pageURL);
  	});

	it('should expect 10 submissions to be in list', function() {
		expect(thePage.tableRowCount()).toEqual(10);
  	});

	it('should expect 4 pages of results', function() {
		// 4 page buttons plus Minus first, last, prev, next buttons
		expect(thePage.pageCount()).toEqual(8);
  	});
	
	it('should expect first site in list to be AFRICOM', function() {
		thePage.selectSiteByIndex(1);
		expect(thePage.selectedSite.getText()).toMatch('AFRICOM');
  	});
	
	it('should expect site selection of DJIBOUTI to contain 4 items', function() {
		thePage.selectSiteByIndex(2);
		expect(thePage.selectedSite.getText()).toMatch('DJIBOUTI');
		expect(thePage.tableRowCount()).toEqual(4);
		thePage.selectSiteByIndex(0); //reset
  	});
	
	it('should expect response selection of Alert to contain 2 items', function() {
		thePage.selectResponseByIndex(3);
		expect(thePage.selectedResponse.getText()).toMatch('Alert');
		expect(thePage.tableRowCount()).toEqual(2);
  	});

	it('should expect response selection of Match to contain only Match items', function() {
		thePage.selectResponseByIndex(2);
		expect(thePage.selectedResponse.getText()).toMatch('Match');
		
		thePage.forAllTableRowsAtColumn(4, function(responseCol) {
  				expect(responseCol.getText()).toMatch('Match');
  		});

		// Reset selection
		thePage.selectResponseByIndex(0);
  	});
	
	
});