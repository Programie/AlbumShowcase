<?php
namespace com\selfcoders\albumshowcase;

class Auth
{
	public static function getUsername()
	{
		return $_SESSION["username"];
	}

	public static function checkLogin($password = null)
	{
		$pdo = DBConnection::getConnection();

		session_start();

		$username = $_POST["username"];

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

		if (Utils::getPasswordHash($password, $row->passwordSalt) != $row->password)
		{
			return false;
		}

		$_SESSION["username"] = $username;
		$_SESSION["password"] = $password;

		return true;
	}
}