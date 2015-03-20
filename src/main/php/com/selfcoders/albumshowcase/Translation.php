<?php
namespace com\selfcoders\albumshowcase;

use Locale;

class Translation
{
	private static $data;

	public static function init()
	{
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

		$filePath = APP_ROOT . "/translation/" . $language . ".json";

		if (file_exists($filePath))
		{
			Translation::$data = json_decode(file_get_contents($filePath));
		}
	}

	/**
	 * Translate the given message
	 *
	 * @param string $message The message to translate
	 *
	 * @return string The translated message
	 */
	public static function translate($message)
	{
		if (isset(Translation::$data->{$message}))
		{
			$message = Translation::$data->{$message};
		}

		return utf8_decode($message);
	}
}