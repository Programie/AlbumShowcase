<?php
require_once __DIR__ . "/includes/config.inc.php";
require_once __DIR__ . "/includes/Database.class.php";
require_once __DIR__ . "/vendor/james-heinrich/getid3/getid3/getid3.php";

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

function getPasswordHash($password, $salt)
{
	$hash = hash("sha512", $password);

	return hash("sha512", $hash . $salt, true);
}

function checkLogin($save = true, $username = null, $password = null)
{
	global $pdo;

	session_start();

	if ($username === null)
	{
		$username = $_POST["username"];
	}

	if ($username === null)
	{
		$username = $_SESSION["username"];
	}

	if ($password === null)
	{
		$password = $_POST["password"];
	}

	if ($password === null)
	{
		$password = $_SESSION["password"];
	}

	$query = $pdo->prepare("
		SELECT `id`, `password`, `passwordSalt`
		FROM `users`
		WHERE `username` = :username
	");

	$query->execute(array
	(
		":username" => $username
	));

	if (!$query->rowCount())
	{
		return false;
	}

	$row = $query->fetch();

	if (getPasswordHash($password, $row->passwordSalt) != $row->password)
	{
		return false;
	}

	if ($save)
	{
		$_SESSION["username"] = $username;
		$_SESSION["password"] = $password;
	}

	return true;
}

if (!isset($_GET["get"]))
{
	header("HTTP/1.1 400 Bad Request");
	exit;
}

$pdo = Database::getConnection();

switch($_GET["get"])
{
	case "checklogin":
		echo json_encode(array
		(
			"ok" => checkLogin(),
			"username" => $_SESSION["username"]
		));
		exit;
	case "changepassword":
		if (!checkLogin(false, $_SESSION["username"], $_POST["currentPassword"]))
		{
			echo json_encode(array
			(
				"ok" => false,
				"reason" => "auth_fail"
			));
			exit;
		}

		if (strtolower($_SESSION["username"]) == "demo")
		{
			echo json_encode(array
			(
				"ok" => false,
				"reason" => "demo_user"
			));
			exit;
		}

		$query = $pdo->prepare("
			UPDATE `users`
			SET `password` = :password, `passwordSalt` = :passwordSalt
			WHERE `username` = :username
		");

		$passwordSalt = rand(0, 10000);

		$query->execute(array
		(
			":username" => $_SESSION["username"],
			":password" => getPasswordHash($_POST["newPassword"], $passwordSalt),
			":passwordSalt" => $passwordSalt
		));

		session_destroy();// Force re-login

		echo json_encode(array
		(
			"ok" => true
		));
		exit;
	case "logout":
		session_start();
		session_destroy();

		echo json_encode(array
		(
			"ok" => true
		));
		exit;
	case "getstats":
		if (!checkLogin())
		{
			header("HTTP/1.1 401 Authorization Required");
			exit;
		}

		if (isset($_GET["end"]))
		{
			$endDate = new DateTime($_GET["end"]);
		}
		else
		{
			$endDate = new DateTime();
		}

		if (isset($_GET["start"]))
		{
			$startDate = new DateTime($_GET["start"]);
		}
		else
		{
			$startDate = clone $endDate;
			$startDate->sub(new DateInterval("P1M"));// Default to the last month
		}

		$period = new DatePeriod($startDate, new DateInterval("P1D"), $endDate);

		/**
		 * @var $date DateTime
		 */
		foreach ($period as $date)
		{
			$data[$date->format("Y-m-d")] = 0;
		}

		if (isset($_GET["id"]))
		{
			$query = $pdo->prepare("
				SELECT `date`
				FROM `downloads`
				WHERE `albumId` = :albumId AND `date` BETWEEN :startDate AND :endDate
			");

			$query->execute(array
			(
				":albumId" => $_GET["id"],
				":startDate" => $startDate->format("Y-m-d H:i:s"),
				":endDate" => $endDate->format("Y-m-d H:i:s")
			));
		}
		else
		{
			$query = $pdo->prepare("
				SELECT `date`
				FROM `downloads`
				WHERE `date` BETWEEN :startDate AND :endDate
			");

			$query->execute(array
			(
				":startDate" => $startDate->format("Y-m-d H:i:s"),
				":endDate" => $endDate->format("Y-m-d H:i:s")
			));
		}

		while ($row = $query->fetch())
		{
			$date = new DateTime($row->date);

			$data[$date->format("Y-m-d")]++;
		}

		$convertedData = array();
		foreach ($data as $date => $count)
		{
			$convertedData[] = array(strtotime($date) * 1000, $count);
		}

		header("Content-Type: application/json");
		echo json_encode(array
		(
			"data" => $convertedData,
			"startDate" => $startDate->getTimestamp() * 1000,
			"endDate" => $endDate->getTimestamp() * 1000
		));
		exit;
	case "albumdata":
		if (!checkLogin())
		{
			header("HTTP/1.1 401 Authorization Required");
			exit;
		}

		if (!isset($_GET["id"]))
		{
			header("HTTP/1.1 400 Bad Request");
			exit;
		}

		$albumId = $_GET["id"];

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

		$albumData = $query->fetch();

		$query = $pdo->prepare("
			SELECT `number`, `artist`, `title`, `length`
			FROM `tracks`
			WHERE `albumId` = :albumId
		");

		$query->execute(array
		(
			":albumId" => $albumId
		));

		$albumData->tracks = array();

		while ($row = $query->fetch())
		{
			$row->number = (int) $row->number;
			$row->length = (int) $row->length;

			$albumData->tracks[] = $row;
		}

		echo json_encode($albumData);
		exit;
	case "savealbum":
		if (!checkLogin())
		{
			header("HTTP/1.1 401 Authorization Required");
			exit;
		}

		$albumId = null;
		if (isset($_GET["id"]))
		{
			$albumId = $_GET["id"];
		}

		$data = json_decode(file_get_contents("php://input"));
		if (!$data)
		{
			header("HTTP/1.1 400 Bad Request");
			exit;
		}

		if (!isset($data->title) or !isset($data->releaseDate) or !isset($data->tracks) or !is_array($data->tracks))
		{
			header("HTTP/1.1 400 Bad Request");
			exit;
		}

		if ($albumId)
		{
			$query = $pdo->prepare("
				UPDATE `albums`
				SET
					`title` = :title,
					`releaseDate` = :releaseDate
				WHERE `id` = :id
			");

			$query->execute(array
			(
				":title" => $data->title,
				":releaseDate" => $data->releaseDate,
				":id" => $albumId
			));
		}
		else
		{
			$query = $pdo->prepare("
				INSERT INTO `albums`
				SET
					`title` = :title,
					`releaseDate` = :releaseDate
			");

			$query->execute(array
			(
				":title" => $data->title,
				":releaseDate" => $data->releaseDate
			));

			$albumId = $pdo->lastInsertId();
		}

		$updateQuery = $pdo->prepare("
			UPDATE `tracks`
			SET
				`number` = :number,
				`artist` = :artist,
				`title` = :title,
				`length` = :length
			WHERE `id` = :id
		");

		$insertQuery = $pdo->prepare("
			INSERT INTO `tracks`
			SET
				`albumId` = :albumId,
				`number` = :number,
				`artist` = :artist,
				`title` = :title,
				`length` = :length
		");

		$query = $pdo->prepare("
			SELECT `id`
			FROM `tracks`
			WHERE `albumId` = :albumId
		");

		$query->execute(array
		(
			":albumId" => $albumId
		));

		$validTracks = array();

		while ($row = $query->fetch())
		{
			$validTracks[$row->id] = false;// Default to invalid
		}

		foreach ($data->tracks as $track)
		{
			if ($track->id)
			{
				$updateQuery->execute(array
				(
					":number" => $track->number,
					":artist" => $track->artist,
					":title" => $track->title,
					":length" => $track->length,
					":id" => $track->id
				));
			}
			else
			{
				$insertQuery->execute(array
				(
					":albumId" => $albumId,
					":number" => $track->number,
					":artist" => $track->artist,
					":title" => $track->title,
					":length" => $track->length
				));

				$track->id = $pdo->lastInsertId();
			}

			$validTracks[$track->id] = true;// This track is still valid
		}

		$query = $pdo->prepare("
			DELETE FROM `tracks`
			WHERE `id` = :id
		");

		foreach ($validTracks as $trackId => $isValid)
		{
			if ($isValid)
			{
				continue;
			}

			// Delete the track from the database if no longer in track list
			$query->execute(array
			(
				":id" => $trackId
			));
		}
		exit;
	case "uploadfile":
		if (!checkLogin())
		{
			header("HTTP/1.1 401 Authorization Required");
			exit;
		}

		if (!isset($_GET["id"]) or !isset($_GET["type"]))
		{
			header("HTTP/1.1 400 Bad Request");
			exit;
		}

		$albumId = $_GET["id"];

		$query = $pdo->prepare("
			SELECT `id`
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

		switch ($_GET["type"])
		{
			case "cover":
				$fileExtension = "jpg";
				break;
			case "upload":
				$fileExtension = "zip";
				break;
			default:
				header("HTTP/1.1 400 Bad Request");
				exit;
		}

		if (!@move_uploaded_file($_FILES["file"]["tmp_name"], __DIR__ . "/albums/" . $albumId . "." . $fileExtension))
		{
			header("HTTP/1.1 500 Internal Server Error");
			exit;
		}

		echo "ok";
		exit;
	case "deletealbum":
		if (!checkLogin())
		{
			header("HTTP/1.1 401 Authorization Required");
			exit;
		}

		if (!isset($_GET["id"]))
		{
			header("HTTP/1.1 400 Bad Request");
			exit;
		}

		$query = $pdo->prepare("
			DELETE FROM `albums`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $_GET["id"]
		));

		echo "ok";
		exit;
	case "allalbums":
		if (!checkLogin())
		{
			header("HTTP/1.1 401 Authorization Required");
			exit;
		}

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

		$list = array();

		while ($row = $query->fetch())
		{
			$row->id = (int) $row->id;

			$downloadsQuery->execute(array
			(
				":albumId" => $row->id
			));

			$row->downloads = (int) $downloadsQuery->fetch()->count;

			$list[] = $row;
		}

		echo json_encode($list);
		exit;
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
			WHERE `releaseDate` <= NOW()
			ORDER BY `releaseDate` DESC
		");

		while ($row = $query->fetch())
		{
			$row->id = (int) $row->id;

			// Downloadable archive file must exist
			if (!file_exists(__DIR__ . "/albums/" . $row->id . ".zip"))
			{
				continue;
			}

			// Cover image must exist
			if (!file_exists(__DIR__ . "/albums/" . $row->id . ".jpg"))
			{
				continue;
			}

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
	case "metadata":
		if (!checkLogin())
		{
			header("HTTP/1.1 401 Authorization Required");
			exit;
		}

		if (!isset($_GET["id"]))
		{
			header("HTTP/1.1 400 Bad Request");
			exit;
		}

		$zipFile = __DIR__ . "/albums/" . $_GET["id"] . ".zip";

		if (!file_exists($zipFile))
		{
			header("HTTP/1.1 404 Not Found");
			exit;
		}

		$zipArchive = new ZipArchive();
		$zipArchive->open($zipFile);

		$list = array();

		for ($index = 0; $index < $zipArchive->numFiles; $index++)
		{
			$tmpFilename = tempnam(sys_get_temp_dir(), "zip");

			unlink($tmpFilename);

			$filename = $zipArchive->getNameIndex($index);

			$pathInfo = pathinfo($filename);

			$tmpFilename .= "." . $pathInfo["extension"];

			copy("zip://" . $zipFile . "#" . $filename, $tmpFilename);

			$id3 = new getID3();

			$fileInfo = $id3->analyze($tmpFilename);

			getid3_lib::CopyTagsToComments($fileInfo);

			unlink($tmpFilename);

			$track = new StdClass;

			if (isset($fileInfo["playtime_seconds"]))
			{
				$track->length = (int) $fileInfo["playtime_seconds"];
			}

			if (isset($fileInfo["comments"]["artist"][0]))
			{
				$track->artist = $fileInfo["comments"]["artist"][0];
			}

			if (isset($fileInfo["comments"]["title"][0]))
			{
				$track->title = $fileInfo["comments"]["title"][0];
			}

			if (isset($fileInfo["comments"]["track"][0]))
			{
				$track->number = (int) $fileInfo["comments"]["track"][0];
			}

			$trackArray = (array) $track;

			if (empty($trackArray))
			{
				continue;
			}

			if (!$track->artist and !$track->title)
			{
				$track->title = $pathInfo["filename"];
			}

			$list[] = $track;
		}

		$zipArchive->close();

		usort($list, function($item1, $item2)
		{
			return $item1->number > $item2->number;
		});

		echo json_encode($list);
		exit;
}

header("HTTP/1.1 400 Bad Request");
exit;