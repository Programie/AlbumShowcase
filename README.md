# Album Showcase

**Note: This software is currently in beta stage! It may eat the Internet.**

## What is it?

Album Showcase is a simple showcase and download page which can be used to provide an overview of your music albums and allow your guests to download the albums.

## Requirements

   * A webserver running at least PHP 5.3
   * A MySQL database (other SQL databases which are supported by PHP/PDO should work as well but are not tested)

## Configuration

   * Copy [includes/config.sample.inc.php](/includes/config.sample.inc.php) to **includes/config.inc.php** and open the copy
   * Configure the variables to match your requirements
   * Import the database schema from [database.sql](/tools/database.sql)
   * Configure your webserver to point to the directory containing the files
   * Make sure the user running the webserver (e.g. www-data) has write permission to the albums folder
   * Create a new user using the [create-user.php](/tools/create-user.php) script located in the [tools directory](/tools) (execute it from the command line).

## Demo

A demo of Album Showcase can be found [here](http://albumshowcase-demo.selfcoders.com). The username and password for the admin area is "demo".