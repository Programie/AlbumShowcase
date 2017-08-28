<?php
use com\selfcoders\albumshowcase\BackendHandler;
use com\selfcoders\albumshowcase\DBConnection;
use com\selfcoders\albumshowcase\service\exception\DemoUserException;
use com\selfcoders\albumshowcase\service\exception\EndpointNotFoundException;
use com\selfcoders\albumshowcase\service\exception\FileUploadException;
use com\selfcoders\albumshowcase\service\exception\ForbiddenException;
use com\selfcoders\albumshowcase\service\exception\MissingDataException;
use com\selfcoders\albumshowcase\service\exception\NotFoundException;
use com\selfcoders\albumshowcase\service\exception\ServiceConfigurationException;

require_once __DIR__ . "/../bootstrap.php";

$pdo = DBConnection::getConnection();

$handler = new BackendHandler();

try
{
	$response = $handler->handleRequest($_SERVER["PATH_INFO"], $_SERVER["REQUEST_METHOD"]);

	if ($response !== null)
	{
		header("Content-Type: application/json");
		echo json_encode($response);
	}
}
catch (DemoUserException $exception)
{
	header("HTTP/1.1 403 Forbidden");
	echo $exception->getCode();
}
catch (EndpointNotFoundException $exception)
{
	header("HTTP/1.1 404 Not Found");
	echo $exception->getCode();
}
catch (FileUploadException $exception)
{
	header("HTTP/1.1 500 Internal Server Error");
	echo $exception->getCode();
}
catch (ForbiddenException $exception)
{
	header("HTTP/1.1 403 Forbidden");
	echo $exception->getCode();
}
catch (MissingDataException $exception)
{
	header("HTTP/1.1 400 Bad Request");
	echo $exception->getCode();
}
catch (NotFoundException $exception)
{
	header("HTTP/1.1 404 Not Found");
	echo $exception->getCode();
}
catch (ServiceConfigurationException $exception)
{
	header("HTTP/1.1 500 Internal Server Error");
	echo $exception->getCode();
	error_log($exception);
}
catch (Exception $exception)
{
	header("HTTP/1.1 500 Internal Server Error");
	echo "Error while executing method!";
	error_log($exception);
}