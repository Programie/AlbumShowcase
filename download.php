<?php
require_once __DIR__ . "/includes/config.inc.php";
require_once __DIR__ . "/includes/Database.class.php";

list($albumId) = explode("/", trim($_SERVER["PATH_INFO"], "/"));// Get the first part (album ID) of the path

if (!$albumId)
{
	header("HTTP/1.1 400 Bad Request");
	exit;
}

$pdo = Database::getConnection();

$query = $pdo->prepare("
	SELECT `title`, `releaseDate`
	FROM `albums`
	WHERE `id` = :id
");

$query->execute(array
(
	":id" => $albumId
));

if (!$query->rowCount())
{
	header("HTTP/1.1 404 Not Found");
	exit;
}

$sourceFilename = __DIR__ . "/albums/" . $albumId . ".zip";

$albumRow = $query->fetch();

header("Content-Description: File Download");
header("Content-Type: application/zip");
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");
header("Content-Length: " . filesize($sourceFilename));

flush();

$file = fopen($sourceFilename, "rb");
while ($chunk = fread($file, 8192))
{
	echo $chunk;
	flush();
}

fclose($file);

if (defined("TRACK_DOWNLOADS") and TRACK_DOWNLOADS)
{
	$query = $pdo->prepare("
		INSERT INTO `downloads`
		SET
			`albumId` = :albumId,
			`date` = NOW(),
			`ipAddress` = :ipAddress
	");

	$query->execute(array
	(
		":albumId" => $albumId,
		":ipAddress" => $_SERVER["REMOTE_ADDR"]
	));
}