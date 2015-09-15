<?php
# -----
# Default RP Server configuration file.
# You'll need to change some of these values before
# your RP Server works correctly. The root path and the
# database settings, specifically.
#
# Configuration variables follow.
# -----

## The server on which the database is located;
## if it's one the same server, use 'localhost'
$rpDBServer = 'localhost';
## Database name
$rpDBName   = 'rp_db';
## The user that has permissions to the database
$rpDBUser   = 'rp_db_user';
## Password for that user; PLEASE make it long and random!
$rpDBPass   = 'abc123';

## Is the admin panel enabled?
$rpAdminPanelEnabled = true;
## Admin panel username and password.
## Uncomment and ***CHANGE*** these when you enable the panel!
$rpAdminPanelUser = 'admin';
$rpAdminPanelPass = 'admin';

## The length of the auto-generated URL for each RP room.
$rpIDLength = 4;
## How many posts should show up on the main RP page.
## Also how many should be on each page of the archive.
$rpPostsPerPage = 20;
## How often (in milliseconds) the page should check the
## server for updates.
$rpRefreshMillis = 3000;

## Admin Broadcast Message
## When you need to deliver a message to the entire site,
## uncomment the following line and set the message text to
## what you want it to say.
## HTML works.
# $rpBroadcast = "<h3>Admin Broadcast</h3><p>This is your captain speaking.</p>";
## Maintenance Message
## When taking the site down for maintenance, uncomment the
## following line and change the message.
## Once maintenance is done, just comment it out again!
## HTML is allowed, again.
# $rpDown = "<p>Sorry! RPNow is down for routine maintenance.</p>";

?>