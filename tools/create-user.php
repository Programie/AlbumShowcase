<?php
require_once __DIR__ . "/../includes/config.inc.php";
require_once __DIR__ . "/../includes/Database.class.php";

if (count($argv) < 2)
{
	echo "Usage: " . $argv[0] . " <username> [<replace>]\n";
	echo "\n\n";
	echo "Example 1: " . $argv[0] . " admin\n";
	echo "Example 2: " . $argv[0] . " admin 1\n";
	exit;
}

$username = $argv[1];
$replace = @$argv[2];

$pdo = Database::getConnection();

$query = $pdo->prepare("
	SELECT `id`
	FROM `users`
	WHERE `username` = :username
");

$query->execute(array
(
	":username" => $username
));

if ($query->rowCount())
{
	if ($replace)
	{
		$row = $query->fetch();

		$query = $pdo->prepare("
			DELETE FROM `users`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $row->id
		));
	}
	else
	{
		die("User '" . $username . "' already exists!");
	}
}

if (strtolower($username) == "demo")
{
	$password = "demo";
}
else
{
	$charset = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	$password = array();
	$charsetLength = strlen($charset);
	for ($character = 0; $character < 10; $character++)
	{
		$password[] = $charset[rand(0, $charsetLength - 1)];
	}

	$password = implode("", $password);
}

$hash = hash("sha512", $password);
$salt = rand(0, 10000);

$query = $pdo->prepare("
	INSERT INTO `users`
	SET
		`username` = :username,
		`password` = :password,
		`passwordSalt` = :passwordSalt
");

$query->execute(array
(
	":username" => $username,
	":password" => hash("sha512", $hash . $salt, true),
	":passwordSalt" => $salt
));

echo "User created!\n";
echo "\n";
echo "Username: " . $username . "\n";
echo "Password: " . $password . "\n";