<?php
namespace com\selfcoders\albumshowcase\service\exception;

class ServiceConfigurationException extends ServiceException
{
	public function __construct()
	{
		$this->code = "configuration_error";
	}
}