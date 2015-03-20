<?php
namespace com\selfcoders\albumshowcase;

use PDO;

/**
 * Class providing a single database connection
 */
class DBConnection
{
	/**
	 * @var PDO Instance of PDO connected to the database
	 */
	private static $pdo;

	/**
	 * Return the previously initialized database connection or initialize a new one to the configured database.
	 *
	 * @return PDO Instance of PDO connected to the database
	 */
	public static function getConnection()
	{
		if (DBConnection::$pdo)
		{
			return DBConnection::$pdo;
		}

		DBConnection::$pdo = new PDO(DATABASE_DSN, DATABASE_USERNAME, DATABASE_PASSWORD);
		DBConnection::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		DBConnection::$pdo->query("SET NAMES utf8");

		return DBConnection::$pdo;
	}
}