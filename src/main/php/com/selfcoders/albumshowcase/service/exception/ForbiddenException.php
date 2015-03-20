<?php
namespace com\selfcoders\albumshowcase\service\exception;

class ForbiddenException extends ServiceException
{
	public function __construct()
	{
		$this->code = "forbidden";

		parent::__construct("You are not allowed to access this resource.");
	}
}