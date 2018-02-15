# RPNow
**RPNow** is an open-source web application that lets anyone quickly create a private chatroom to roleplay with their friends.

___Note:__ This project has been superseded by [rpnow2](https://github.com/rpnow/rpnow2), and is __no longer receiving updates.___

## Features
Noteworthy features of RPNow include:
* No registration process
* Entirely in-browser; no downloads for the user
* Works on both desktop and mobile
* Requires no browser plugins such as Flash or Java
* Updates in real time; no refreshing needed
* Flashing banner alerts when a post is made
* Browse the archive of all posts from beginning to end
* Download a text backup of the RP at any time

## Requirements
* Docker (Tested with Docker 17.04.0)
* Docker-compose (Tested with 1.10.0)

## Running
1.  `docker-compose build`
2.  `docker-compose run --rm build composer install`
3.  `docker-compose up`
4.  To enable the admin panel, uncomment the appropriate lines in the config.php file. _Be sure to change the admin username and password!_

## Credits
RPNow was created almost entirely by me, [Nigel Nelson](http://nigelnelson.me).

For additional information on software and libraries used, as well as a list of donors and other contributors, see [credits.md](CREDITS.MD).

## License
RPNow is licensed under the [GNU General Public License (3.0)](LICENSE)
