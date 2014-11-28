<?php
/**
 * This script can be used to generate demo albums (e.g. to see how the application looks like).
 */

require_once __DIR__ . "/../includes/config.inc.php";
require_once __DIR__ . "/../includes/Database.class.php";

function writeLog($string)
{
	echo "[" . date("Y-m-d H:i:s") . "] " . $string . "\n";
}

$pdo = Database::getConnection();

$albumQuery = $pdo->prepare("
	INSERT INTO `albums`
	SET
		`title` = :title,
		`releaseDate` = :releaseDate
");

$downloadQuery = $pdo->prepare("
	INSERT INTO `downloads`
	SET
		`albumId` = :albumId,
		`date` = :date,
		`ipAddress` = :ipAddress
");

$trackQuery = $pdo->prepare("
	INSERT INTO `tracks`
	SET
		`albumId` = :albumId,
		`number` = :number,
		`artist` = :artist,
		`title` = :title,
		`length` = :length
");

$albums = array
(
	array
	(
		"title" => "My Album",
		"releaseDate" => date("Y") . "-05-01",
		"tracks" => array
		(
			array
			(
				"title" => "My Song",
				"artist" => "DJ Me",
				"length" => 197
			),
			array
			(
				"title" => "My Song (Club Mix)",
				"artist" => "DJ Me",
				"length" => 267
			),
			array
			(
				"title" => "My Song (Extended Mix)",
				"artist" => "DJ Me",
				"length" => 345
			)
		)
	),
	array
	(
		"title" => "We found Samples",
		"releaseDate" => date("Y") . "-09-16",
		"tracks" => array
		(
			array
			(
				"title" => "We found Samples (Radio Mix)",
				"artist" => "The missing Sample",
				"length" => 201
			),
			array
			(
				"title" => "We found Samples (Club Mix)",
				"artist" => "The missing Sample feat. DJ Sample",
				"length" => 319
			),
			array
			(
				"title" => "We found Samples (Extended Mix)",
				"artist" => "The missing Sample feat. No Sampler",
				"length" => 449
			),
			array
			(
				"title" => "We found no Samples",
				"artist" => "The missing Sample",
				"length" => 268
			)
		)
	),
	array
	(
		"title" => "Resampled",
		"releaseDate" => date("Y") . "-10-20",
		"tracks" => array
		(
			array
			(
				"title" => "No Samples no Cry",
				"artist" => "Bob Simply",
				"length" => 231
			),
			array
			(
				"title" => "We found Samples",
				"artist" => "Semplhana",
				"length" => 242
			),
			array
			(
				"title" => "Sample Man",
				"artist" => "K Sample",
				"length" => 201
			)
		)
	),
	array
	(
		"title" => "The best Samples",
		"releaseDate" => date("Y") . "-12-01",
		"tracks" => array
		(
			array
			(
				"title" => "We found Samples",
				"artist" => "The missing Sample",
				"length" => 196
			),
			array
			(
				"title" => "My Song",
				"artist" => "DJ Me",
				"length" => 197
			),
			array
			(
				"title" => "No Samples no Cry",
				"artist" => "Bob Simply",
				"length" => 231
			)
		)
	)
);

foreach ($albums as $album)
{
	writeLog("Adding album: " . $album["title"]);

	$albumQuery->execute(array
	(
		":title" => $album["title"],
		":releaseDate" => $album["releaseDate"]
	));

	$albumId = $pdo->lastInsertId();

	writeLog("Album ID: " . $albumId);

	$totalLength = 0;

	foreach ($album["tracks"] as $trackIndex => $track)
	{
		$totalLength += $track["length"];

		writeLog("Adding track: " . $track["artist"] . " - " . $track["title"]);

		$trackQuery->execute(array
		(
			":albumId" => $albumId,
			":number" => $trackIndex + 1,
			":artist" => $track["artist"],
			":title" => $track["title"],
			":length" => $track["length"]
		));
	}

	$sizeMb = $totalLength / 60 * 1.5;

	// Create an example file
	writeLog("Generating download file with " . round($sizeMb, 1) . " MB");

	$file = fopen(__DIR__ . "/../albums/" . $albumId . ".zip", "w");
	if ($file !== false)
	{
		fseek($file, $sizeMb * 1024 * 1024, SEEK_CUR);
		fwrite($file, 0);
		fclose($file);
	}

	// Copy the sample album cover
	copy(__DIR__ . "/sample-album.jpg", __DIR__ . "/../albums/" . $albumId . ".jpg");

	// Create random fake downloads
	$downloads = rand(0, 100000);

	writeLog("Generating " . $downloads . " fake downloads");

	for ($download = 1; $download < $downloads; $download++)
	{
		$ipAddress = array();

		for ($ipPart = 0; $ipPart < 4; $ipPart++)
		{
			$ipAddress[] = rand(0, 255);
		}

		$downloadQuery->execute(array
		(
			":albumId" => $albumId,
			":date" => date("Y-m-d H:i:s", rand(strtotime($album["releaseDate"]), time())),
			":ipAddress" => implode(".", $ipAddress)
		));
	}
}