# roleplay-now
RP Now is an open-source web application that lets anyone quickly create a private chatroom to roleplay with their friends.

## Features
Noteworthy features of this RP chat program include:
* No registration process
* Entirely in-browser; no downloads
* Works on both desktop and mobile
* Requires no browser plugins such as Flash or Java
* Updates in real time; no refreshing needed
* Flashing banner alerts when a post is made
* Browse the archive of all posts from beginning to end
* Download a text backup of the RP at any time

## Technologies
The server-side implementation of RP Now is built on the LAMP stack. Specifically, it uses PHP, a MySQL database, and an Apache .htaccess file to configure routing.

The front-end uses only HTML, CSS, and Javascript. This means that the user needs no plugins (such as Flash or Java) in order to use this applicaiton. All they need is a semi-modern web browser. This means that RP Now will also work on many mobile devices.

Additional libraries are listed below:

* [Slim](http://www.slimframework.com), a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs.
* [Twig](http://twig.sensiolabs.org), the flexible, fast, and secure template engine for PHP.
* [jQuery](https://jquery.com), a fast, small, and feature-rich JavaScript library.
* [Spectrum](https://bgrins.github.io/spectrum), the no-hassle jQuery colorpicker.
* [Moment.js](http://momentjs.com): Parse, validate, manipulate, and display dates in JavaScript.

## Installing
Right now, there's a lot of manual setup that needs to take place. I intend to document this better in the near future when the process has been reasonably solidified.

Here is a general idea of what needs to be done:

1. Download the repository
2. Retrieve the Slim framework
3. Retrieve the extra Views add-on for the Slim framework
4. Retrieve the Twig templating engine
5. Retrieve the HttpBasicAuth middleware from Slim-Extras and put it in the Slim/Middleware folder
6. Put these all in the correct locations in the directory
7. Create a MySQL database and import the database in the /docs/ folder
8. Give a user SELECT and INSERT permissions on that database
9. Update config.php with the correct database information, as well as the path to the main folder
10. If you have an SSL certificate for your site, uncomment the HTTPS segment in the .htaccess file

## License
RP Now is licensed under the [GNU General Public License V3.0](https://www.gnu.org/licenses/gpl-3.0-standalone.html).
