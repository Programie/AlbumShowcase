<?php
namespace com\selfcoders\albumshowcase\service\exception;

class DemoUserException extends ServiceException
{
	public function __construct()
	{
		$this->code = "demo_user";
	}
}