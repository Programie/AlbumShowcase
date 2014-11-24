<?php
require_once __DIR__ . "/config.inc.php";

/**
 * Class providing a single database connection
 */
class Database
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
		if (Database::$pdo)
		{
			return Database::$pdo;
		}

		Database::$pdo = new PDO(DATABASE_DSN, DATABASE_USERNAME, DATABASE_PASSWORD);
		Database::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		Database::$pdo->query("SET NAMES utf8");

		return Database::$pdo;
	}
}