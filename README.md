# imPulse

**imPulse** Group Collaboration Software

## Objective
Attempting to take a product that was developed using Java for the server and Adobe Flex for the client and migrate it to PHP and AngularJS.  

## Setup
*Requires PHP >= 5.5.0 and MySQL >= 5.5.2*

- Create a database named **impulse**
- Use/Select the database **impulse**

In the **db** folder
- Create the tables by running **impulse-schema.sql** 
- Add an admin user by running **admin-user.sql** 
  - _This will create a user ID admin with password *change.me*_  
- Add test data for properties and system_setting tables by running **test-data.sql**

- Edit **config.inc.php** and set the DB_ values to connect to your database server

In the **test** folder run the [PHPUnit](https://phpunit.de/) tests:
- `phpunit UserServicePDOTest.php`
- `phpunit ForumServicePDOTest.php`
- `phpunit SettingsServicePDOTest.php`


## Web Server Setup
The web server deployment folder looks like this so far (webroot -> source folder):
- /impulse 
  - The root directory on the web server 
- /impulse/api       ->    /server/src  
  - REST web services API 
- /impulse/admin     -> /ui/admin  
  - Admin Application 
- /impulse/common     -> /ui/common  
  - Shared UI components 
- /impulse/vendor     -> /ui/vendor  
  - Vendor supplied code, AngularJS, Bootstrap, etc. 
- /impulse/workspace  -> /ui/workspace  
  - Workspace Collaboration Application 
- /impulse/index.html -> /ui/index.html  
  - Landing Page 

## 2014-10-29
Added administrative application.  
*From the ui/ folder:*
- Use [Bower](http://bower.io/) to retrieve dependencies.
  - `bower install`

- Use [Grunt](http://bower.io/) to move the files into the dist folder.  
  - `grunt copy`

## Initial Release
There is nothing useful here at this point but the server code demonstrating the use of:
- [Slim Framework 2.4.2](http://www.slimframework.com) for implementing REST web services and authentication middleware
- [NotORM](http://www.notorm.com/) for mapping arrays to database fields and getting rid of some repetitive PDO code 


  