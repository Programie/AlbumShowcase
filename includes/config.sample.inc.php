<?php
/**
 * This is the sample configuration file
 * Copy this file and name it "config.inc.php"
 */

// Database connection
define("DATABASE_DSN", "mysql:host=localhost;dbname=albumshowcase");
define("DATABASE_USERNAME", "root");
define("DATABASE_PASSWORD", "");

// Title of the page (Displayed as page title and in the header section)
define("PAGE_TITLE", "Album Showcase");

// Set what info to show in the badge in the download button (Remove to remove the badge)
//define("DOWNLOAD_BADGE", "count");// Show the number of downloads
define("DOWNLOAD_BADGE", "size");// Show the file size of the download

// Set whether to track downloads (false or remove the definition to disable download tracking)
define("TRACK_DOWNLOADS", true);

// Set whether to show the admin login button (false or remove the definition to hide it)
define("SHOW_ADMIN_LOGIN", true);