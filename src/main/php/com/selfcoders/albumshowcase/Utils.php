<?php
namespace com\selfcoders\albumshowcase;

class Utils
{
	/**
	 * Format the given file size in bytes to a human readable format.
	 *
	 * @param int $size The file size to format in bytes
	 * @param int $precision The optional number of decimal digits to round to
	 * @return string The formatted file size (e.g. "4.7G")
	 */
	public static function formatFileSize($size, $precision = 1)
	{
		$units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");

		$useUnit = null;

		foreach ($units as $index => $unit)
		{
			if ($size < 1024)
			{
				$useUnit = $unit;
				break;
			}

			$size /= 1024;
		}

		if ($useUnit == null)
		{
			$useUnit = end($units);
		}

		return round($size, $precision) . " " . $useUnit;
	}

	public static function getPasswordHash($password, $salt)
	{
		$hash = hash("sha512", $password);

		return hash("sha512", $hash . $salt, true);
	}

	/**
	 * Short the given number to make it human readable.
	 *
	 * @param int|float $value The number to short
	 * @return string The number suffixed by "K" and divided by 1000 if it is larger than or equal to 1000
	 */
	public static function shortNumber($value)
	{
		if ($value < 1000)
		{
			return $value;
		}

		if ($value < 1000 * 1000)
		{
			return floor($value / 1000) . "K";
		}

		return "999K+";
	}
}