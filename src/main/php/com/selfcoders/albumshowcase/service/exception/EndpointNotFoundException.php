<?php
namespace com\selfcoders\albumshowcase\service\exception;

class EndpointNotFoundException extends ServiceException
{
	protected $path;
	protected $method;

	public function __construct($path, $method)
	{
		$this->path = $path;
		$this->method = $method;

		$this->code = "endpoint_not_found";

		parent::__construct("Endpoint '" . $this->path . "' with method '" . $this->method . "' does not exist");
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getMethod()
	{
		return $this->method;
	}
}