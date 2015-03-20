<?php
use com\selfcoders\albumshowcase\Translation;

require_once __DIR__ . "/vendor/autoload.php";

define("APP_ROOT", __DIR__);
define("APP_SOURCE_ROOT", APP_ROOT . "/src/main/php");
define("HTTP_DOCS_ROOT", APP_ROOT . "/httpdocs");

require_once APP_ROOT . "/config/config.php";

/**
 * Shorthand for Translation::translate
 *
 * @param string $message The message to translate
 *
 * @return string The translated message
 */
function tr($message)
{
	return Translation::translate($message);
}

Translation::init();