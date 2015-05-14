Prerequisites:
There must be a user with ID testuser0 and password of change.me in the system with no forums.


Start test web server: /usr/local/lib/node_modules/protractor/bin/webdriver-manager start
Run tests: 
protractor conf.js --suite login (to fully test login)
protractor conf.js --suite all (to run all test specs with a quick login)
