<?php
require_once __DIR__ . "/includes/config.inc.php";
require_once __DIR__ . "/includes/Database.class.php";

/**
 * Short the given number to make it human readable.
 *
 * @param int|float $value The number to short
 * @return string The number suffixed by "K" and divided by 1000 if it is larger than or equal to 1000
 */
function shortNumber($value)
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

/**
 * Format the given file size in bytes to a human readable format.
 *
 * @param int $size The file size to format in bytes
 * @param int $precision The optional number of decimal digits to round to
 * @return string The formatted file size (e.g. "4.7G")
 */
function formatFileSize($size, $precision = 1)
{
	$units = array("B", "K", "M", "G", "T", "P", "E", "Z", "Y");

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

	return round($size, $precision) . $useUnit;
}

if (!isset($_GET["get"]))
{
	header("HTTP/1.1 400 Bad Request");
	exit;
}

$pdo = Database::getConnection();

switch($_GET["get"])
{
	case "albums":
		$list = array();

		$downloadsQuery = $pdo->prepare("
			SELECT COUNT(`id`) AS `count`
			FROM `downloads`
			WHERE `albumId` = :albumId
		");

		$query = $pdo->query("
			SELECT `id`, `title`, `releaseDate`
			FROM `albums`
			ORDER BY `releaseDate` DESC
		");
		while ($row = $query->fetch())
		{
			$row->id = (int) $row->id;

			if (defined("DOWNLOAD_BADGE"))
			{
				switch (DOWNLOAD_BADGE)
				{
					case "count":
						$downloadsQuery->execute(array(":albumId" => $row->id));

						$row->downloadBadge = shortNumber($downloadsQuery->fetch()->count);
						break;
					case "size":
						$row->downloadBadge = formatFileSize(filesize(__DIR__ . "/albums/" . $row->id . ".zip"));
						break;
				}
			}

			$list[] = $row;
		}

		echo json_encode($list);
		exit;
	case "tracklist":
		if (!isset($_GET["id"]))
		{
			header("HTTP/1.1 400 Bad Request");
			exit;
		}

		$query = $pdo->prepare("
			SELECT `number`, `artist`, `title`, `length`
			FROM `tracks`
			WHERE `albumId` = :albumId
		");

		$query->execute(array
		(
			":albumId" => $_GET["id"]
		));

		if (!$query->rowCount())
		{
			header("HTTP/1.1 404 Not Found");
			exit;
		}

		$list = array();

		while ($row = $query->fetch())
		{
			$row->number = (int) $row->number;
			$row->length = (int) $row->length;

			$list[] = $row;
		}

		echo json_encode($list);
		exit;
}

header("HTTP/1.1 400 Bad Request");
exit;