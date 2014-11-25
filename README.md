# Album Showcase

**Note: This software is in an early development stage and not ready for production yet!**

## What is it?

Album Showcase is a simple showcase and download page which can be used to provide an overview of your music albums and allow your guests to download the albums.

## Requirements

   * A webserver running at least PHP 5.3
   * A MySQL database (other SQL databases which are supported by PHP/PDO should work as well but are not tested)

## Configuration

   * Copy **includes/config.sample.inc.php** to **includes/config.inc.php** and open the copy
   * Configure the variables to match your requirements
   * Import the database schema from **tools/database.sql**
   * Configure your webserver to point to the directory containing the files