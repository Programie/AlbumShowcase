<?php
require_once __DIR__ . "/../includes/config.inc.php";
require_once __DIR__ . "/../includes/Database.class.php";

if (count($argv) < 2)
{
	die("Usage: " . $argv[0] . " <username>");
}

$username = $argv[1];

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
	die("User '" . $username . "' already exists!");
}

$charset = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
$password = array();
$charsetLength = strlen($charset);
for ($character = 0; $character < 10; $character++)
{
	$password[] = $charset[rand(0, $charsetLength - 1)];
}

$password = implode("", $password);

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