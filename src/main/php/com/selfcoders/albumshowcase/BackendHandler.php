<?php
namespace com\selfcoders\albumshowcase;

use com\selfcoders\albumshowcase\router\Router;
use com\selfcoders\albumshowcase\router\Target;
use com\selfcoders\albumshowcase\service\AbstractService;
use com\selfcoders\albumshowcase\service\annotation\NoInputDataDecode;
use com\selfcoders\albumshowcase\service\annotation\RequireLogin;
use com\selfcoders\albumshowcase\service\annotation\RequireRelogin;
use com\selfcoders\albumshowcase\service\exception\EndpointNotFoundException;
use com\selfcoders\albumshowcase\service\exception\ForbiddenException;
use com\selfcoders\albumshowcase\service\exception\ServiceConfigurationException;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass;
use ReflectionMethod;

class BackendHandler
{
	public function handleRequest($path, $method)
	{
		$router = new Router();

		$router->map(HttpMethod::GET, "/download/[i:id]/[:name].zip", new Target("Albums", "getFile"));

		$router->map(HttpMethod::GET, "/albums", new Target("Albums", "getReleasedAlbums"));
		$router->map(HttpMethod::GET, "/albums/all", new Target("Albums", "getAll"));
		$router->map(HttpMethod::POST, "/albums", new Target("Albums", "createAlbum"));
		$router->map(HttpMethod::GET, "/albums/[i:id]", new Target("Albums", "getDetails"));
		$router->map(HttpMethod::PUT, "/albums/[i:id]", new Target("Albums", "editAlbum"));
		$router->map(HttpMethod::DELETE, "/albums/[i:id]", new Target("Albums", "deleteAlbum"));
		$router->map(HttpMethod::GET, "/albums/[i:id]/metadata", new Target("Albums", "getMetaData"));
		$router->map(HttpMethod::GET, "/albums/[i:id]/tracks", new Target("Albums", "getTrackList"));
		$router->map(HttpMethod::GET, "/albums/[i:id]/stats", new Target("Albums", "getStats"));
		$router->map(HttpMethod::GET, "/albums/[i:id]/cover.jpg", new Target("Albums", "getCover"));
		$router->map(HttpMethod::POST, "/albums/[i:id]/cover.jpg", new Target("Albums", "setCover"));// TODO: Change to PUT
		$router->map(HttpMethod::POST, "/albums/[i:id]/file.zip", new Target("Albums", "setFile"));// TODO: Change to PUT

		$router->map(HttpMethod::GET, "/user/login", new Target("User", "checkLogin"));
		$router->map(HttpMethod::POST, "/user/login", new Target("User", "checkLogin"));
		$router->map(HttpMethod::GET, "/user/logout", new Target("User", "logout"));
		$router->map(HttpMethod::POST, "/user/password", new Target("User", "changePassword"));

		$match = $router->match($path, $method);
		if ($match === false)
		{
			throw new EndpointNotFoundException($path, $method);
		}

		/**
		 * @var $target Target
		 */
		$target = $match["target"];

		$classPath = "com\\selfcoders\\albumshowcase\\service\\" . $target->class;

		if (!class_exists($classPath))
		{
			throw new ServiceConfigurationException("Configured class does not exist: " . $target->class);
		}

		$reflection = new ReflectionClass($classPath);

		if ($reflection->isAbstract())
		{
			throw new ServiceConfigurationException("Configured class is abstract: " . $target->class);
		}

		/**
		 * @var $serviceClassInstance AbstractService
		 */
		$serviceClassInstance = $reflection->newInstance();

		if (!method_exists($serviceClassInstance, $target->method))
		{
			throw new ServiceConfigurationException("Configured method does not exist in " . $target->class . ": " . $target->method);
		}

		$serviceClassInstance->parameters = (object) $match["params"];

		$reflectionMethod = new ReflectionMethod($serviceClassInstance, $target->method);

		AnnotationRegistry::registerAutoloadNamespace("com\\selfcoders\\albumshowcase\\service\\annotation", APP_SOURCE_ROOT);

		$reader = new AnnotationReader();

		$decodeInputData = true;

		/**
		 * @var $annotation Annotation
		 */
		foreach ($reader->getMethodAnnotations($reflectionMethod) as $annotation)
		{
			if ($annotation instanceof RequireLogin)
			{
				if (!Auth::checkLogin())
				{
					throw new ForbiddenException;
				}
			}

			if ($annotation instanceof RequireRelogin)
			{
				if (!Auth::checkLogin($_POST["password"]))
				{
					throw new ForbiddenException;
				}
			}

			if ($annotation instanceof NoInputDataDecode)
			{
				$decodeInputData = false;
			}
		}

		if ($decodeInputData)
		{
			$serviceClassInstance->data = json_decode(file_get_contents("php://input"));
		}

		return $reflectionMethod->invoke($serviceClassInstance);
	}
}