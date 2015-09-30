<?php

##-----
##
## Default RPNow configuration file.
##
## You MUST provide database credentials in order for the
## server to work!
##
## Other default configuration settings work out-of-the-box,
## but can be modified if you wish. This includes things such
## as enabling the admin panel, changing the length of the
## URLs for each RP, and displaying a broadcast message to all
## users on the site.
##
##-----

## The server on which the database is located;
##  If it's the same as the webserver, use 'localhost'.
$rpDBServer = 'localhost';
## Database name
$rpDBName   = 'rp_db';
## A user with SELECT and INSERT permissions to that database.
$rpDBUser   = 'rp_db_user';
## Password for that user; PLEASE make it long and random!
$rpDBPass   = 'abc123';

## Uncomment the following line to enable the admin panel.
# $rpAdminPanelEnabled = true;
## Admin panel username and password.
## Uncomment and ***CHANGE*** these when you enable the panel!
# $rpAdminPanelUser = 'admin';
# $rpAdminPanelPass = 'admin';

## The length of the auto-generated URL for each RP room.
##  Minimum recommended for a live website is 4.
##  Maximum allowed is 20.
$rpIDLength = 7;
## The characters to be used in that URL.
##  By default, certain ambiguous characters (such as O and 0)
##  are excluded entirely.
$rpIDChars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
## How many posts long should an archive page be?
##  Also determines how many messages are retained on the
##  chat page simultaneously
$rpPostsPerPage = 20;
## How often (in milliseconds) the chat page should check the
##  server for updates.
$rpRefreshMillis = 3000;

## Admin Broadcast Message
##  When you need to deliver a message to the entire site,
##  uncomment the following line and set the message text to
##  what you want it to say.
##  HTML works.
# $rpBroadcast = "<h3>Admin Broadcast</h3><p>This is your captain speaking.</p>";

## Maintenance Message
##  When taking the site down for maintenance, uncomment the
##  following line and change the message.
##  Once maintenance is done, just comment it out again!
##  HTML is allowed, again.
# $rpDown = "<p>Temporarily down for maintenance; sorry!</p>";

?>