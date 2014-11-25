<?php
require_once __DIR__ . "/includes/config.inc.php";
require_once __DIR__ . "/includes/Database.class.php";

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

		$query = $pdo->query("SELECT `id`, `title`, `releaseDate` FROM `albums`");
		while ($row = $query->fetch())
		{
			$row->id = (int) $row->id;
			$row->releaseDate = date(DATE_FORMAT, strtotime($row->releaseDate));

			$downloadsQuery->execute(array
			(
				":albumId" => $row->id
			));

			$row->downloadCount = $downloadsQuery->fetch()->count;

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