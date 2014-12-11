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

		$query = $pdo->query("
			SELECT `id`, `title`, `releaseDate`
			FROM `albums`
			ORDER BY `releaseDate` DESC
		");

		$list = array();

		while ($row = $query->fetch())
		{
			$row->id = (int) $row->id;

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
			WHERE `releaseDate` >= NOW()
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