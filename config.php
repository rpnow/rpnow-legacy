<?php
# -----
# Default RP Server configuration file.
# You'll need to change some of these values before
# your RP Server works correctly. The root path and the
# database settings, specifically.
#
# Configuration variables follow.
# -----

## The URL base path to the directory containing this
## website. Should probably end in a forward slash.
$rpRootPath = '/';

## The server on which the database is located;
## if it's one the same server, use 'localhost'
$rpDBServer = 'localhost';
## Database name
$rpDBName   = 'rp_db';
## The user that has permissions to the database
$rpDBUser   = 'rp_db_user';
## Password for that user; PLEASE make it long and random!
$rpDBPass   = 'abc123';

## The length of the auto-generated URL for each RP room.
$rpIDLength = 4;
## How many posts should show up on the main RP page.
## Also how many should be on each page of the archive.
$rpPostsPerPage = 20;
## How often (in milliseconds) the page should check the
## server for updates.
$rpRefreshMillis = 3000;

?>