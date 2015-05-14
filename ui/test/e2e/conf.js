exports.config = {
   seleniumAddress: 'http://localhost:4444/wd/hub',
   chromeOnly: true,
   chromeDriver: '/usr/local/lib/node_modules/protractor/selenium/chromedriver',
   capabilities: {
      browserName: 'chrome',
      'chromeOptions': {
         args: [ '--test-type' ]
      }
   },
   suites: {
      login: 'full-login-test.js',
      all: [ 'quick-login.js', '*.spec.js' ]
   },

   jasmineNodeOpts: {
      showColors: true,
      defaultTimeoutInterval: 30000
   }
//,specs: ['login.spec.js', 'dashboard.spec.js', 'forum-crud.spec.js']
};
