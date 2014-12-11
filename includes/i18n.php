<?php
require_once __DIR__ . "/Translation.class.php";

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

if (isset($_GET["lang"]))
{
	$language = $_GET["lang"];
}
elseif (class_exists("Locale"))// Locale class is part of the Internationalization (intl) PECL extension
{
	$language = Locale::lookup(array
	(
		"de",
		"en"
	), Locale::acceptFromHttp($_SERVER["HTTP_ACCEPT_LANGUAGE"]));
}
elseif (defined("LANGUAGE"))
{
	$language = LANGUAGE;
}
else
{
	$language = "en";
}

Translation::init($language);