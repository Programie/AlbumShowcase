<?php
namespace com\selfcoders\albumshowcase\service;

use com\selfcoders\albumshowcase\Auth;
use com\selfcoders\albumshowcase\service\annotation\RequireLogin;
use com\selfcoders\albumshowcase\service\annotation\RequireRelogin;
use com\selfcoders\albumshowcase\service\exception\DemoUserException;
use com\selfcoders\albumshowcase\Utils;

class User extends AbstractService
{
	/**
	 * @RequireRelogin
	 */
	public function changePassword()
	{
		if (strtolower(Auth::getUsername()) == "demo")
		{
			throw new DemoUserException;
		}

		$query = $this->pdo->prepare("
			UPDATE `users`
			SET `password` = :password, `passwordSalt` = :passwordSalt
			WHERE `username` = :username
		");

		$passwordSalt = rand(0, 10000);

		$query->execute(array
		(
			":username" => Auth::getUsername(),
			":password" => Utils::getPasswordHash($_POST["newPassword"], $passwordSalt),
			":passwordSalt" => $passwordSalt
		));

		session_destroy();// Force re-login

		return true;
	}

	/**
	 * @RequireLogin
	 */
	public function checkLogin()
	{
		// This method just checks the login

		return true;
	}

	/**
	 * @RequireLogin
	 */
	public function logout()
	{
		session_start();
		session_destroy();

		return true;
	}
}