# RPNow
**RPNow** is an open-source web application that lets anyone quickly create a private chatroom to roleplay with their friends.

## Features
Noteworthy features of RPNow include:
* No registration process
* Entirely in-browser; no downloads
* Works on both desktop and mobile
* Requires no browser plugins such as Flash or Java
* Updates in real time; no refreshing needed
* Flashing banner alerts when a post is made
* Browse the archive of all posts from beginning to end
* Download a text backup of the RP at any time

## Requirements
* Apache with mod_rewrite and .htaccess enabled
* PHP >= 5.3.0
* MySQL
* SSL certificate (optional)

## Installing
1. Download the RPNow repository from GitHub
2. Place the contents of the /rpnow/ directory somewhere on the target web server
3. Import the MySQL database from /docs/rpnow.sql
4. Give a user SELECT and INSERT permissions on that database
5. Update config.php with the correct database information, as well as the path to the main folder
6. If you have an SSL certificate for your site, uncomment the HTTPS segment in the .htaccess file
7. To enable the admin panel, uncomment the appropriate lines in the config.php file. _Be sure to change the admin username and password!_

## Credits
For additional information on the contributors, libraries, and references, see [about.html](rpnow/templates/about.html).
