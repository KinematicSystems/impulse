# imPulse

**imPulse** Group Collaboration Software

## Objective
Attempting to take a product that was developed using Java for the server and Adobe Flex for the client and migrate it to PHP and AngularJS.  

There is nothing useful here at this point but the server code demostrating the use of:
[Slim Framework 2.4.2](http://www.slimframework.com) for implementing REST web services 
[NotORM](http://www.notorm.com/) for mapping arrays to database fields and getting rid of some repetitive PDO code 

## Setup
Requires PHP version 5.5.0 or greater
Requires MySQL version 5.5.2 or greater

- Create a database named *impulse*
- Use/Select the database *impulse*

In the *db* folder
- Create the tables by running *impulse-schema.sql* 
- Add an admin user by running *admin-user.sql* _this will create a user ID admin with password *change.me*_ 
- Add test data for properties and system_setting tables by running *test-data.sql*

- Edit *config.inc.php* and set the DB_ values to connect to your database server

In the test folder run tests
- `phpunit UserServicePDOTest.php`
- `phpunit ForumServicePDOTest.php`
- `phpunit SettingsServicePDOTest.php`

The REST services are accessed from the <webroot>/impulse/api URL 
The web server deployment folder looks like this so far:
<webroot>/impulse
<webroot>/impulse/api -> <projectdir>/server/src
<webroot>/impulse/admin -> <projectdir>/ui/admin
<webroot>/impulse/common -> <projectdir>/ui/common
<webroot>/impulse/vendor -> <projectdir>/ui/vendor
<webroot>/impulse/workspace -> <projectdir>/ui/workspace
<webroot>/impulse/index.html -> <projectdir>/ui/index.html
