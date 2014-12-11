<?php
class Translation
{
	private static $data;

	public static function init($language)
	{
		$filePath = __DIR__ . "/../translation/" . $language . ".json";

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