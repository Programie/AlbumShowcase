<?php
namespace com\selfcoders\albumshowcase\service;

use com\selfcoders\albumshowcase\service\annotation\RequireLogin;
use com\selfcoders\albumshowcase\service\exception\FileUploadException;
use com\selfcoders\albumshowcase\service\exception\MissingDataException;
use com\selfcoders\albumshowcase\service\exception\NotFoundException;
use com\selfcoders\albumshowcase\Track;
use com\selfcoders\albumshowcase\Utils;
use DateInterval;
use DatePeriod;
use DateTime;
use getID3;
use getid3_lib;
use ZipArchive;

class Albums extends AbstractService
{
	private function buildTrackList($albumId)
	{
		$query = $this->pdo->prepare("
			SELECT `number`, `artist`, `title`, `length`
			FROM `tracks`
			WHERE `albumId` = :albumId
		");

		$query->execute(array
		(
			":albumId" => $albumId
		));

		$list = array();

		while ($row = $query->fetch())
		{
			$track = new Track();

			$track->number = (int) $row->number;
			$track->title = $row->title;
			$track->artist = $row->artist;
			$track->length = (int) $row->length;

			$list[] = $track;
		}

		return $list;
	}

	private function updateTracks($albumId, $tracks)
	{
		$updateQuery = $this->pdo->prepare("
			UPDATE `tracks`
			SET
				`number` = :number,
				`artist` = :artist,
				`title` = :title,
				`length` = :length
			WHERE `id` = :id
		");

		$insertQuery = $this->pdo->prepare("
			INSERT INTO `tracks`
			SET
				`albumId` = :albumId,
				`number` = :number,
				`artist` = :artist,
				`title` = :title,
				`length` = :length
		");

		$query = $this->pdo->prepare("
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

		/**
		 * @var $track Track
		 */
		foreach ($tracks as $track)
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

				$track->id = $this->pdo->lastInsertId();
			}

			$validTracks[$track->id] = true;// This track is still valid
		}

		$query = $this->pdo->prepare("
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
	}

	/**
	 * @RequireLogin
	 */
	public function createAlbum()
	{
		if (!isset($this->data->title) or !isset($this->data->artist) or !isset($this->data->releaseDate) or !isset($this->data->tracks) or !is_array($this->data->tracks))
		{
			throw new MissingDataException;
		}

		$query = $this->pdo->prepare("
			INSERT INTO `albums`
			SET
				`title` = :title,
				`artist` = :artist,
				`releaseDate` = :releaseDate
		");

		$query->execute(array
		(
			":title" => $this->data->title,
			":artist" => $this->data->artist,
			":releaseDate" => $this->data->releaseDate
		));

		$albumId = $this->pdo->lastInsertId();

		$this->updateTracks($albumId, $this->data->tracks);

		header("HTTP/1.1 201 Created");

		return null;
	}

	/**
	 * @RequireLogin
	 */
	public function deleteAlbum()
	{
		$query = $this->pdo->prepare("
			DELETE FROM `albums`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $this->parameters->id
		));

		$filePath = APP_ROOT . "/albums/" . $this->parameters->id;

		if (file_exists($filePath . ".jpg"))
		{
			unlink($filePath . ".jpg");
		}

		if (file_exists($filePath . ".zip"))
		{
			unlink($filePath . ".zip");
		}
	}

	/**
	 * @RequireLogin
	 */
	public function editAlbum()
	{
		if (!isset($this->data->title) or !isset($this->data->artist) or !isset($this->data->releaseDate) or !isset($this->data->tracks) or !is_array($this->data->tracks))
		{
			throw new MissingDataException;
		}

		$query = $this->pdo->prepare("
			UPDATE `albums`
			SET
				`title` = :title,
				`artist` = :artist,
				`releaseDate` = :releaseDate
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":title" => $this->data->title,
			":artist" => $this->data->artist,
			":releaseDate" => $this->data->releaseDate,
			":id" => $this->parameters->id
		));

		$this->updateTracks($this->parameters->id, $this->data->tracks);

		header("HTTP/1.1 201 Created");

		return null;
	}

	/**
	 * @RequireLogin
	 */
	public function getAll()
	{
		$downloadsQuery = $this->pdo->prepare("
			SELECT COUNT(`id`) AS `count`
			FROM `downloads`
			WHERE `albumId` = :albumId
		");

		$query = $this->pdo->query("
			SELECT `id`, `title`, `artist`, `releaseDate`
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

		return $list;
	}

	public function getCover()
	{
		$query = $this->pdo->prepare("
			SELECT `id`
			FROM `albums`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $this->parameters->id
		));

		if (!$query->rowCount())
		{
			throw new NotFoundException;
		}

		$filename = APP_ROOT . "/albums/" . $this->parameters->id . ".jpg";

		if (!file_exists($filename))
		{
			throw new NotFoundException;
		}

		header("Content-Type: image/jpeg");
		readfile($filename);

		return null;
	}

	/**
	 * @RequireLogin
	 */
	public function getDetails()
	{
		$query = $this->pdo->prepare("
			SELECT `title`, `artist`, `releaseDate`
			FROM `albums`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $this->parameters->id
		));

		if (!$query->rowCount())
		{
			throw new NotFoundException;
		}

		$albumData = $query->fetch();

		$albumData->tracks = $this->buildTrackList($this->parameters->id);

		return $albumData;
	}

	public function getFile()
	{
		$query = $this->pdo->prepare("
			SELECT `id`
			FROM `albums`
			WHERE `id` = :id AND `releaseDate` <= NOW()
		");

		$query->execute(array
		(
			":id" => $this->parameters->id
		));

		if (!$query->rowCount())
		{
			throw new NotFoundException;
		}

		$sourceFilename = APP_ROOT . "/albums/" . $this->parameters->id . ".zip";

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
			$query = $this->pdo->prepare("
				INSERT INTO `downloads`
				SET
					`albumId` = :albumId,
					`date` = NOW(),
					`ipAddress` = :ipAddress
			");

			$query->execute(array
			(
				":albumId" => $this->parameters->id,
				":ipAddress" => $_SERVER["REMOTE_ADDR"]
			));
		}

		return null;
	}

	/**
	 * @RequireLogin
	 */
	public function getMetaData()
	{
		$zipFile = __DIR__ . "/albums/" . $this->parameters->id . ".zip";

		if (!file_exists($zipFile))
		{
			throw new NotFoundException;
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

			$track = new Track;

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

			if (!$track->isValid())
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

		return $list;
	}

	public function getReleasedAlbums()
	{
		$list = array();

		$downloadsQuery = $this->pdo->prepare("
			SELECT COUNT(`id`) AS `count`
			FROM `downloads`
			WHERE `albumId` = :albumId
		");

		$query = $this->pdo->query("
			SELECT `id`, `title`, `artist`, `releaseDate`
			FROM `albums`
			WHERE `releaseDate` <= NOW()
			ORDER BY `releaseDate` DESC
		");

		while ($row = $query->fetch())
		{
			$row->id = (int) $row->id;

			// Downloadable archive file must exist
			if (!file_exists(APP_ROOT . "/albums/" . $row->id . ".zip"))
			{
				continue;
			}

			// Cover image must exist
			if (!file_exists(APP_ROOT . "/albums/" . $row->id . ".jpg"))
			{
				continue;
			}

			if (defined("DOWNLOAD_BADGE"))
			{
				switch (DOWNLOAD_BADGE)
				{
					case "count":
						$downloadsQuery->execute(array
						(
							":albumId" => $row->id
						));

						$row->downloadBadge = Utils::shortNumber($downloadsQuery->fetch()->count);
						break;
					case "size":
						$row->downloadBadge = utils::formatFileSize(filesize(APP_ROOT . "/albums/" . $row->id . ".zip"));
						break;
				}
			}

			$list[] = $row;
		}

		return $list;
	}

	/**
	 * @RequireLogin
	 */
	public function getStats()
	{
		if (isset($this->parameters->endDate))
		{
			$endDate = new DateTime($this->parameters->endDate);
		}
		else
		{
			$endDate = new DateTime();
		}

		if (isset($this->parameters->startDate))
		{
			$startDate = new DateTime($this->parameters->startDate);
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

		if (isset($this->parameters->id))
		{
			$query = $this->pdo->prepare("
				SELECT `date`
				FROM `downloads`
				WHERE `albumId` = :albumId AND `date` BETWEEN :startDate AND :endDate
			");

			$query->execute(array
			(
				":albumId" => $this->parameters->id,
				":startDate" => $startDate->format("Y-m-d H:i:s"),
				":endDate" => $endDate->format("Y-m-d H:i:s")
			));
		}
		else
		{
			$query = $this->pdo->prepare("
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

		$data = array();

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

		return array
		(
			"data" => $convertedData,
			"startDate" => $startDate->getTimestamp() * 1000,
			"endDate" => $endDate->getTimestamp() * 1000
		);
	}

	public function getTrackList()
	{
		$list = $this->buildTracklist($this->parameters->id);

		if (empty($list))
		{
			throw new NotFoundException;
		}

		return $list;
	}

	/**
	 * @RequireLogin
	 */
	public function setCover()
	{
		$query = $this->pdo->prepare("
			SELECT `id`
			FROM `albums`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $this->parameters->id
		));

		if (!$query->rowCount())
		{
			throw new NotFoundException;
		}

		if (!@move_uploaded_file($_FILES["file"]["tmp_name"], APP_ROOT . "/albums/" . $this->parameters->id . ".jpg"))
		{
			throw new FileUploadException;
		}

		header("HTTP/1.1 201 Created");

		return null;
	}

	/**
	 * @RequireLogin
	 */
	public function setFile()
	{
		$query = $this->pdo->prepare("
			SELECT `id`
			FROM `albums`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $this->parameters->id
		));

		if (!$query->rowCount())
		{
			throw new NotFoundException;
		}

		if (!@move_uploaded_file($_FILES["file"]["tmp_name"], APP_ROOT . "/albums/" . $this->parameters->id . ".zip"))
		{
			throw new FileUploadException;
		}

		header("HTTP/1.1 201 Created");

		return null;
	}
}
