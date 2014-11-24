<?php
require_once __DIR__ . "/includes/Database.class.php";

if (!isset($_GET["get"]))
{
	header("HTTP/1.1 400 Bad Request");
	exit;
}

$pdo = Database::getConnection();

switch($_GET["get"])
{
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